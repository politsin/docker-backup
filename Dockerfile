FROM ubuntu:20.04
MAINTAINER Synapse <mail@synapse-studio.ru>

# Surpress Upstart errors/warning
RUN dpkg-divert --local --rename --add /sbin/initctl
RUN ln -sf /bin/true /sbin/initctl
# Let the conatiner know that there is no tty
ENV DEBIAN_FRONTEND noninteractive

# APT install:::
RUN apt update && \
    apt install -y software-properties-common \
                   dnsutils \
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
                   unzip && \
    apt install -y sqlite3 \
                   mysql-client \
                   postgresql-client &&  \
    apt install -y awscli \
                   python3-pip && \
    apt autoremove -y && \
    apt clean && \
    apt autoclean && \
    mkdir /var/run/sshd && \
    echo -n > /var/lib/apt/extended_states && \
    rm -rf /var/lib/apt/lists/* && \
    rm -rf /usr/share/man/?? && \
    rm -rf /usr/share/man/??_*

#PHP:::
RUN apt update && \
    LC_ALL=C.UTF-8 add-apt-repository -y ppa:ondrej/php && \
    apt update && \
    apt install -y php8.1 \
                   php8.1-zip \
                   php8.1-curl \
                   php8.1-mysql \
                   php8.1-pgsql \
                   php8.1-mbstring \
                   php-json \
                   php-ssh2 \
                   php-sqlite3 && \
    apt autoremove -y && \
    apt clean && \
    apt autoclean && \
    rm -rf /var/lib/apt/lists/* && \
    rm -rf /usr/share/man/?? && \
    rm -rf /usr/share/man/??_*

#DRUSH:::
RUN wget https://github.com/drush-ops/drush-launcher/releases/latest/download/drush.phar -q -O drush \
    && chmod +x drush \
    && mv drush /usr/local/bin/drush

#Composer:::
RUN wget https://getcomposer.org/installer -q -O composer-setup.php \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && chmod +x /usr/local/bin/composer

#COPY script & config:::
COPY console/console.php /var/www/console.php
COPY console/composer.json /var/www/composer.json
RUN cd /var/www/ && composer install

#Fix ownership
RUN chmod 755 /var/www/console.php

ENTRYPOINT ["/usr/bin/php", "/var/www/console.php"]
