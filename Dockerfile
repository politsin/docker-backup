FROM ubuntu:16.04
MAINTAINER Synapse <mail@synapse-studio.ru>

# Surpress Upstart errors/warning
RUN dpkg-divert --local --rename --add /sbin/initctl
RUN ln -sf /bin/true /sbin/initctl
# Let the conatiner know that there is no tty
ENV DEBIAN_FRONTEND noninteractive

#APT-GET:::
RUN apt-get update && \
    apt-get install -y software-properties-common apt-utils curl wget && \
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
RUN wget https://github.com/drush-ops/drush/releases/download/8.1.16/drush.phar -q -O drush \
    && php drush core-status \
    && chmod +x drush \
    && mv drush /usr/local/bin/drush

#AWS:::
RUN pip install awscli

#COPY script & config:::
COPY start.py /start.py

#Fix ownership
RUN chmod 755 /start.py && \
    mkdir /run/php && \
    chown -R www-data.www-data /run/php

ENTRYPOINT ["/start.py"]
