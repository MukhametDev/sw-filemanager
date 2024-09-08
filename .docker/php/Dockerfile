# Используем PHP 8.2 FPM как базовый образ
FROM php:8.2-fpm

# Устанавливаем рабочую директорию
WORKDIR /var/www

# Устанавливаем необходимые PHP-расширения, такие как pdo_mysql, zip и необходимые утилиты
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install pdo_mysql zip

# Устанавливаем Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Копируем пользовательский php.ini в контейнер
COPY .docker/php/php.ini /usr/local/etc/php/php.ini

# Копируем только файлы для установки зависимостей
COPY composer.json composer.lock ./

# Устанавливаем зависимости с помощью Composer
RUN composer install --no-scripts --no-autoloader --prefer-dist --no-dev

# Копируем проект в контейнер
COPY . .

# Выполняем компиляцию автозагрузчика
RUN composer dump-autoload --optimize

# Устанавливаем права на файлы
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www

# Команда для запуска php-fpm
CMD ["php-fpm"]
