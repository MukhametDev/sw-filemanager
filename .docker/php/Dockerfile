FROM php:8.2-fpm

COPY .docker/php/zz-docker.conf /usr/local/etc/php-fpm.d/zz-docker.conf

RUN apt-get update && apt-get install -y \
    zip \
    unzip \
    git 

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/src

RUN docker-php-ext-install pdo_mysql

COPY .docker/php/php.ini /usr/local/etc/php/php.ini

RUN mkdir -p /var/run/php && chmod -R 777 /var/run/php && chmod -R 777 /var/www

CMD ["php-fpm"]