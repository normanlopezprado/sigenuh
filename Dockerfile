# syntax=docker/dockerfile:1
FROM php:8.2-fpm

ARG DEBIAN_FRONTEND=noninteractive

# 1) Paquetes base + PHP ext (incluye freetype/jpeg p/ gd) + Nginx + Supervisor + MariaDB client
RUN apt-get update && apt-get install -y --no-install-recommends \
      nginx supervisor git unzip curl ca-certificates gnupg \
      mariadb-client \
      libzip-dev libpng-dev libonig-dev libxml2-dev \
      libfreetype6-dev libjpeg62-turbo-dev \
  && docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-install -j"$(nproc)" pdo_mysql zip gd \
  && rm -rf /var/lib/apt/lists/*

# 2) Node.js 20 (vía NodeSource) + Yarn
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
  && apt-get update && apt-get install -y --no-install-recommends nodejs \
  && npm install -g yarn \
  && npm cache clean --force \
  && rm -rf /var/lib/apt/lists/*

# 3) Composer
RUN curl -sS https://getcomposer.org/installer | php -- \
      --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html

EXPOSE 80 5173

# Usamos el entrypoint montado por volumen; si no tiene +x, lo forzamos al vuelo.
CMD ["/bin/sh","-lc","chmod +x /usr/local/bin/app-entrypoint 2>/dev/null || true; /usr/local/bin/app-entrypoint"]
