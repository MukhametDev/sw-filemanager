FROM php:8.2-fpm

WORKDIR /var/www

RUN docker-php-ext-install pdo_mysql

COPY .docker/php/php.ini /usr/local/etc/php/php.ini

CMD ["php-fpm"]
