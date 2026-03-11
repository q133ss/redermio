FROM php:8.2-fpm-alpine

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

RUN apk add --no-cache \
        bash \
        freetype-dev \
        git \
        icu-dev \
        libjpeg-turbo-dev \
        libpng-dev \
        libzip-dev \
        oniguruma-dev \
        postgresql-dev \
        unzip \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
    && docker-php-ext-install \
        gd \
        intl \
        pdo \
        pdo_pgsql \
        pgsql \
        zip

COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-custom.ini

WORKDIR /var/www/backend
