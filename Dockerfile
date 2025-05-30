FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpng-dev \
    libzip-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libwebp-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install pdo_mysql zip exif \
    && pecl install redis \
    && docker-php-ext-enable redis 

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html

COPY composer.json ./

RUN composer update --no-scripts --no-interaction --optimize-autoloader

COPY . .

RUN if [ ! -f .env ]; then \
      cp .env.example .env; \
      php artisan key:generate; \
    fi

RUN composer require --dev phpunit/phpunit:^9.5.8 --no-scripts
RUN composer run-script post-install-cmd
RUN test -f vendor/bin/phpunit && vendor/bin/phpunit --version

RUN chown -R www-data:www-data storage \
    && chmod +x vendor/bin/phpunit \
    && git config --global --add safe.directory /var/www/html

COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh 

EXPOSE 80
CMD ["apache2-foreground"]
