FROM php:8.4-fpm-alpine

# Extensions système nécessaires pour Laravel
RUN apk add --no-cache postgresql-client \
    postgresql-client \
    nginx \
    supervisor \
    postgresql-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    libxml2-dev \
    libsodium-dev \
    curl-dev \
    nodejs \
    npm \
    git \
    curl \
    bash \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_pgsql \
        pgsql \
        gd \
        zip \
        bcmath \
        intl \
        opcache \
        pcntl \
        mbstring \
        xml \
        curl \
        sodium \
        fileinfo \
        exif

# Configuration PHP pour production
RUN echo "memory_limit = 512M" > /usr/local/etc/php/conf.d/memory.ini \
    && echo "upload_max_filesize = 20M" >> /usr/local/etc/php/conf.d/memory.ini \
    && echo "post_max_size = 25M" >> /usr/local/etc/php/conf.d/memory.ini \
    && echo "max_execution_time = 300" >> /usr/local/etc/php/conf.d/memory.ini

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copier SEULEMENT composer.json et composer.lock d'abord (pour cache Docker)
COPY composer.json composer.lock* ./

# Configurer Composer
ENV COMPOSER_ALLOW_SUPERUSER=1

# Installer les dépendances PHP (le lock file existe maintenant)
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction --prefer-dist --ignore-platform-reqs

# Copier le reste du code
COPY . .

# Générer l'autoloader optimisé
RUN composer dump-autoload --optimize --no-dev --classmap-authoritative --ignore-platform-reqs

# Build assets frontend
RUN if [ -f package.json ]; then npm install && npm run build && rm -rf node_modules; fi

# Nettoyage
RUN php artisan optimize:clear || true

# Permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Config files
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 8080
CMD ["/entrypoint.sh"]
