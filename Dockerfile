#######################################
# Base stage - Common dependencies
#######################################
FROM php:8.3-apache AS base

# Set working directory
WORKDIR /var/www/project

# Set timezone
ENV TZ=Europe/Paris \
    COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_NO_INTERACTION=1

# Install system dependencies and PHP extensions in a single layer
RUN set -eux; \
    apt-get update && apt-get install -y --no-install-recommends \
        # Locales
        locales \
        # Build tools
        apt-utils \
        git \
        unzip \
        # PHP extension dependencies
        libicu-dev \
        g++ \
        libpng-dev \
        libxml2-dev \
        libzip-dev \
        libonig-dev \
        libxslt-dev \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libwebp-dev \
        libxpm-dev \
        libldap2-dev \
        libsasl2-dev \
        # Timezone
        tzdata \
        # SSL
        ssl-cert \
        openssl \
    && echo "en_US.UTF-8 UTF-8" > /etc/locale.gen \
    && echo "fr_FR.UTF-8 UTF-8" >> /etc/locale.gen \
    && locale-gen \
    # Configure PHP extensions
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-configure ldap --with-libdir=lib/x86_64-linux-gnu/ \
    && docker-php-ext-configure pcntl --enable-pcntl \
    # Install PHP extensions
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
        opcache \
        intl \
        zip \
        calendar \
        dom \
        mbstring \
        xsl \
        pcntl \
        gd \
        ldap \
        bcmath \
    # Install PECL extensions
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    # Clean up
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Apache configuration
RUN set -eux; \
    echo "ServerName localhost" >> /etc/apache2/apache2.conf \
    && /usr/sbin/make-ssl-cert generate-default-snakeoil \
    && a2enmod ssl rewrite headers \
    && rm -rf /var/lib/apt/lists/*

# Copy Apache VirtualHost configuration
COPY apache-vhost.conf /etc/apache2/sites-available/000-default.conf

#######################################
# Development stage
#######################################
FROM base AS development

# Install Symfony CLI
RUN curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | bash \
    && apt-get update \
    && apt-get install -y symfony-cli \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Set appropriate permissions for development
RUN chown -R www-data:www-data /var/www/project

# Use www-data user
USER www-data

#######################################
# Production stage
#######################################
FROM base AS production

# Copy composer files
COPY --chown=www-data:www-data composer.json composer.lock symfony.lock ./

# Install dependencies (production only, optimized)
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-progress --prefer-dist \
    && composer clear-cache

# Copy application files
COPY --chown=www-data:www-data . .

# Set permissions
RUN chmod +x bin/console \
    && mkdir -p var/cache var/log \
    && chown -R www-data:www-data var

# Set APP_ENV for build time
ENV APP_ENV=prod \
    APP_DEBUG=0

# Warm up cache (skip if no .env file, it will be handled at runtime)
RUN if [ -f .env ]; then \
        composer run-script --no-dev post-install-cmd; \
    else \
        echo "Skipping post-install scripts (no .env file)"; \
    fi

# Use www-data user
USER www-data

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=40s --retries=3 \
    CMD curl -f http://localhost/health || exit 1

