# PHP backend image (deployed on Render).
FROM php:8.2-apache

RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip libzip-dev libssl-dev libcurl4-openssl-dev pkg-config ca-certificates \
    && docker-php-ext-install pdo_mysql zip \
    && pecl install redis mongodb \
    && docker-php-ext-enable redis mongodb \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock* ./
RUN composer install --no-dev --no-interaction --optimize-autoloader

COPY php ./php

# Render expects the service on port 10000
RUN sed -i 's/Listen 80/Listen 10000/' /etc/apache2/ports.conf \
    && sed -i 's/:80>/:10000>/' /etc/apache2/sites-available/000-default.conf

# Pass the Authorization header through to PHP
RUN echo 'SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1' > /etc/apache2/conf-available/auth-header.conf \
    && a2enconf auth-header

RUN echo '<?php header("Content-Type: application/json"); echo json_encode(["service" => "user-profile-api", "status" => "ok"]);' > /var/www/html/index.php \
    && printf 'Options -Indexes\n' > /var/www/html/.htaccess

EXPOSE 10000
