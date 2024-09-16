FROM php:8.2-fpm

# Копируем конфиг PHP-FPM
COPY .docker/php/zz-docker.conf /usr/local/etc/php-fpm.d/zz-docker.conf

# Устанавливаем системные зависимости и расширения PHP
RUN apt-get update && apt-get install -y \
    zip \
    unzip \
    git \
    && docker-php-ext-install pdo_mysql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Устанавливаем Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Устанавливаем рабочую директорию
WORKDIR /var/www/

# Копируем файлы приложения
COPY . .

# Настраиваем права доступа и владельца файлов
RUN chown -R www-data:www-data /var/www/ && chmod -R 775 /var/www/storage

# Создаем директорию для PHP сокета и настраиваем права доступа
RUN mkdir -p /var/run/php && chown -R www-data:www-data /var/run/php

# Устанавливаем зависимости Composer
RUN composer install --no-interaction --optimize-autoloader

# Запуск PHP-FPM
CMD ["php-fpm"]
