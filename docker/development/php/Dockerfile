FROM php:7.4-cli-alpine

RUN apk update && apk add --no-cache git unzip zip zlib-dev \
    git \
    unzip \
    libzip-dev

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/bin --filename=composer --quiet \
    && composer global require hirak/prestissimo --no-plugins --no-scripts \
    && rm -rf /root/.composer/cache

ENV COMPOSER_ALLOW_SUPERUSER 1

RUN docker-php-ext-install zip

WORKDIR /app
