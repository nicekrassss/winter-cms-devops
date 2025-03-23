FROM php:8.2-apache

RUN apt-get update && apt-get install -y ca-certificates curl gnupg \
    && mkdir -p /etc/apt/keyrings \
    && curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg \
    && echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_18.x nodistro main" | tee /etc/apt/sources.list.d/nodesource.list \
    && apt-get update \
    && apt-get install -y nodejs \
    && npm install -g npm@latest

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
    && docker-php-ext-install pdo_mysql zip exif gd \
    && pecl install redis \
    && docker-php-ext-enable redis 

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY . /var/www/html

COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

RUN composer install  --optimize-autoloader --no-interaction

RUN git config --global --add safe.directory /var/www/html

RUN chown -R www-data:www-data /var/www/html/storage

EXPOSE 80
CMD ["apache2-foreground"]
