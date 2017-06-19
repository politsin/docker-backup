FROM ubuntu:16.04
MAINTAINER Synapse <mail@synapse-studio.ru>

# Surpress Upstart errors/warning
RUN dpkg-divert --local --rename --add /sbin/initctl
RUN ln -sf /bin/true /sbin/initctl
# Let the conatiner know that there is no tty
ENV DEBIAN_FRONTEND noninteractive

#APT-GET:::
RUN apt-get update && \
    apt-get install -y software-properties-common apt-utils curl && \
    apt-get install -y php7.0 \
                       php7.0-fpm \
                       php7.0-dev \
                       php7.0-cgi \
                       php7.0-mysql \
                       php7.0-pgsql \
                       php-sqlite3 \
                       python-pip \
                       mysql-client \
                       postgresql-client && \
    apt-get remove --purge -y software-properties-common && \
    apt-get autoremove -y && \
    apt-get clean && \
    apt-get autoclean && \
    mkdir /var/run/sshd && \
    echo -n > /var/lib/apt/extended_states && \
    rm -rf /var/lib/apt/lists/* && \
    rm -rf /usr/share/man/?? && \
    rm -rf /usr/share/man/??_*

#DRUSH:::
RUN wget https://s3.amazonaws.com/files.drush.org/drush.phar -q -O drush \
    && php drush core-status \
    && chmod +x drush \
    && mv drush /usr/local/bin/drush

#AWS:::
RUN pip install awscli

#COPY script & config:::
COPY config/php/www.conf /etc/php/7.0/fpm/pool.d/www.conf
COPY config/php/php.ini /etc/php/7.0/fpm/php.ini
COPY config/php/opcache.ini /etc/php/7.0/mods-available/opcache.ini
COPY config/start.sh /start.sh

#Fix ownership
RUN chmod 755 /start.sh && \
    mkdir /run/php && \
    chown -R www-data.www-data /run/php && \
    chown www-data.www-data /var/spool/cron/crontabs/www-data && \
    chmod 0777 /var/spool/cron/crontabs && \
    chmod 0600 /var/spool/cron/crontabs/www-data

ENTRYPOINT ["/start.sh"]
