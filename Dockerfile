# ======================
# Stage 1 — Frontend build
# ======================
FROM node:22-alpine AS frontend

WORKDIR /app

COPY package*.json ./
RUN npm install

COPY . .
RUN npm run build

# debug (sementara)
RUN ls -lah public && ls -lah public/build || true


# ======================
# Stage 2 — PHP app
# ======================
FROM php:8.4-cli

WORKDIR /app

RUN apt-get update && \
    apt-get install -y \
        unzip \
        git \
        libzip-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        zip \
        gd

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY . .

RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction

# copy hasil build FE
COPY --from=frontend /app/public ./public

EXPOSE 8989

CMD php artisan key:generate --force && php artisan migrate --force && php artisan db:seed --force && php artisan storage:link && php artisan serve --host=0.0.0.0 --port=8989