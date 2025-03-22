FROM php:8.1-apache

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install pdo_mysql zip exif gd

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY . /var/www/html

RUN composer install --no-dev --optimize-autoloader

RUN composer install --dev --optimize-autoloader

RUN chown -R www-data:www-data /var/www/html/storage

EXPOSE 80
CMD ["apache2-foreground"]
