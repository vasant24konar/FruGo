FROM php:8.2-fpm

# Use pre-built extension binaries — avoids compiling from source
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN chmod +x /usr/local/bin/install-php-extensions \
    && apt-get update && apt-get install -y --no-install-recommends \
        default-mysql-client \
        curl \
        unzip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* \
    && install-php-extensions \
        pdo_mysql \
        mbstring \
        zip \
        opcache

# Install Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

ENV COMPOSER_MEMORY_LIMIT=-1

WORKDIR /var/www

# Copy dependency manifests first for layer caching
COPY composer.json composer.lock* ./

RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

COPY . .

RUN composer dump-autoload --optimize --no-dev

RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage \
    && chmod -R 755 /var/www/bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]
