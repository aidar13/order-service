FROM php:8.3-fpm-alpine

RUN apk add --no-cache git unzip $PHPIZE_DEPS linux-headers \
    && docker-php-ext-install pdo_mysql pcntl bcmath \
    && pecl install redis && docker-php-ext-enable redis \
    && apk del $PHPIZE_DEPS

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-scripts --prefer-dist
COPY . .
RUN composer dump-autoload --optimize && chown -R www-data:www-data storage bootstrap/cache
