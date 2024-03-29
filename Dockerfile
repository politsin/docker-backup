FROM ubuntu:20.04
LABEL maintainer="Synapse <mail@synapse-studio.ru>"

# Surpress Upstart errors/warning
RUN dpkg-divert --local --rename --add /sbin/initctl
RUN ln -sf /bin/true /sbin/initctl
# Let the conatiner know that there is no tty
ENV DEBIAN_FRONTEND noninteractive

#APT install:::
RUN apt update && \
    apt install -y software-properties-common \
                   dnsutils \
                   apt-utils \
                   net-tools \
                   inetutils-ping && \
    apt install -y mc \
                   git \
                   nnn \
                   zip \
                   zsh \
                   curl \
                   htop \
                   nano \
                   ncdu \
                   sass \
                   putty \
                   unzip \
                   ssmtp \
                   sshpass \
                   imagemagick &&  \
    apt install -y sqlite3 \
                   redis-tools \
                   mysql-client && \
    apt install -y awscli \
                   python-is-python3 \
                   python3-pip && \
    apt autoremove -y && \
    apt clean && \
    apt autoclean && \
    mkdir /var/run/sshd && \
    echo -n > /var/lib/apt/extended_states && \
    rm -rf /var/lib/apt/lists/* && \
    rm -rf /usr/share/man/?? && \
    rm -rf /usr/share/man/??_*

#APT postgresql-15:::
RUN wget --quiet -O - https://www.postgresql.org/media/keys/ACCC4CF8.asc | apt-key add - && \
    echo "deb http://apt.postgresql.org/pub/repos/apt/ `lsb_release -cs`-pgdg main" | tee /etc/apt/sources.list.d/pgdg.list && \
    apt update && \
    apt install -y postgresql-client-15 && \
    apt autoremove -y && \
    apt clean && \
    apt autoclean

#PHP:::
RUN apt update && \
    LC_ALL=C.UTF-8 add-apt-repository -y ppa:ondrej/php && \
    apt update && \
    apt install -y php8.1 \
                   php8.1-xml \
                   php8.1-dev \
                   php8.1-dom \
                   php8.1-zip \
                   php8.1-curl \
                   php8.1-mysql \
                   php8.1-pgsql \
                   php8.1-mbstring \
                   php-xml \
                   php-json \
                   php-pear \
                   php-ssh2 \
                   php-redis \
                   php-sqlite3 && \
    apt autoremove -y && \
    apt clean && \
    apt autoclean && \
    rm -rf /var/lib/apt/lists/* && \
    rm -rf /usr/share/man/?? && \
    rm -rf /usr/share/man/??_*

#Redis:::
RUN pecl channel-update pecl.php.net && \
    pecl install redis

#DRUSH:::
RUN wget https://github.com/drush-ops/drush-launcher/releases/latest/download/drush.phar -q -O drush && \
    chmod +x drush && \
    mv drush /usr/local/bin/drush

#Composer:::
RUN wget https://getcomposer.org/installer -q -O composer-setup.php && \
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer && \
    chmod +x /usr/local/bin/composer

#COPY script & config:::
COPY console /opt/console

RUN composer install -d /opt/console
RUN ls /opt/console/vendor

#Fix ownership
RUN chmod 755 /opt/console/console.php

ENTRYPOINT ["/usr/bin/php", "/opt/console/console.php"]
