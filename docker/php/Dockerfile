FROM php:8.2-fpm-alpine

RUN apk add --no-cache \
        bash \
        git \
        icu-dev \
        libzip-dev \
        oniguruma-dev \
        postgresql-dev \
        unzip \
    && docker-php-ext-install \
        intl \
        pdo \
        pdo_pgsql \
        pgsql \
        zip

COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-custom.ini

WORKDIR /var/www/backend
