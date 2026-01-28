FROM php:8.3-apache

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        locales apt-utils git libicu-dev g++ libpng-dev libxml2-dev \
        libzip-dev libonig-dev libxslt-dev ghostscript supervisor unzip \
        libfreetype6-dev libjpeg62-turbo-dev libwebp-dev libxpm-dev \
        libldap2-dev libsasl2-dev

RUN echo "en_US.UTF-8 UTF-8" > /etc/locale.gen && \
    echo "fr_FR.UTF-8 UTF-8" >> /etc/locale.gen && \
    locale-gen

RUN curl -sSk https://getcomposer.org/installer | php -- --disable-tls && \
   mv composer.phar /usr/local/bin/composer

RUN docker-php-ext-configure pcntl --enable-pcntl
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp
RUN docker-php-ext-configure ldap --with-libdir=lib/x86_64-linux-gnu/
RUN docker-php-ext-install pdo pdo_mysql opcache intl zip calendar dom mbstring xsl pcntl gd ldap bcmath

RUN pecl install apcu && docker-php-ext-enable apcu

WORKDIR /var/www/project
RUN curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | bash && \
    apt install -y symfony-cli

# Set timezone
RUN apt-get update -y && apt-get install -y tzdata
ENV TZ=Europe/Paris

# SSL setup
RUN apt-get update -y && apt-get install -y ssl-cert openssl && \
    /usr/sbin/make-ssl-cert generate-default-snakeoil && \
    /usr/sbin/a2enmod ssl rewrite

# Apache configuration
RUN a2enmod headers
RUN a2enmod rewrite

# Copy Apache VirtualHost configuration
COPY apache-vhost.conf /etc/apache2/sites-available/000-default.conf

