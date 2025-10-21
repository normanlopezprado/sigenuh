# docker/app/Dockerfile
FROM php:8.3-fpm-bookworm

# Paquetes base
RUN apt-get update && apt-get install -y \
    git unzip curl gnupg ca-certificates \
    libzip-dev libpng-dev libjpeg-dev libfreetype6-dev \
    libonig-dev libicu-dev libxml2-dev mariadb-client \
 && rm -rf /var/lib/apt/lists/*

# Extensiones PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j$(nproc) pdo_mysql zip gd intl exif pcntl

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Node 20 + Corepack (Yarn)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
 && apt-get update && apt-get install -y nodejs \
 && corepack enable

WORKDIR /var/www/html

COPY docker/app/entrypoint.sh /usr/local/bin/app-entrypoint
RUN chmod +x /usr/local/bin/app-entrypoint

ENTRYPOINT ["/usr/local/bin/app-entrypoint"]
CMD ["php-fpm", "-F"]
