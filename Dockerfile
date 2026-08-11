# syntax=docker/dockerfile:1

########################################
# 1) Frontend assets (Vite build)
########################################
FROM node:20-alpine AS frontend
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY resources resources
COPY vite.config.js ./
RUN npm run build


########################################
# 2) PHP dependencies + app + compiled extensions.
#    Extensions are compiled once here and copied (not rebuilt) into the
#    runtime stage below, since both stages share the same php:8.3-fpm-alpine
#    base/ABI - this halves the compile time an identical second
#    docker-php-ext-install in the runtime stage would otherwise cost.
########################################
FROM php:8.3-fpm-alpine AS backend

RUN apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        libzip-dev \
        oniguruma-dev \
        icu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Dependency layer cached separately from app source so `composer.lock`
# bumps are the only thing that invalidates the slow `composer install`.
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction --prefer-dist

COPY . .
COPY --from=frontend /app/public/build ./public/build

# post-autoload-dump (package:discover, filament:upgrade) needs a bootable
# app, so it runs in this stage with a throwaway .env - this file never
# reaches the runtime image, Coolify's real env vars are the only ones used.
RUN cp .env.example .env \
    && composer install --no-dev --optimize-autoloader --classmap-authoritative --no-interaction --prefer-dist \
    && php artisan key:generate --ansi \
    && rm .env


########################################
# 3) Runtime: php-fpm + nginx + supervisor in one lean container.
#    No compiler/toolchain here - just the shared libs the extensions
#    compiled in "backend" need at load time.
########################################
FROM php:8.3-fpm-alpine AS runtime

RUN apk add --no-cache \
        nginx \
        supervisor \
        libpng \
        libjpeg-turbo \
        freetype \
        libzip \
        oniguruma \
        icu-libs \
        icu-data-full

COPY --from=backend /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
COPY --from=backend /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/

COPY docker/opcache.ini /usr/local/etc/php/conf.d/zz-opcache.ini
COPY docker/www.conf /usr/local/etc/php-fpm.d/zz-www.conf
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisor/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

WORKDIR /var/www/html
COPY --from=backend /var/www/html /var/www/html

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=20s --retries=3 \
    CMD wget -qO- http://127.0.0.1/up >/dev/null || exit 1

ENTRYPOINT ["entrypoint.sh"]
CMD ["supervisord", "-c", "/etc/supervisor/supervisord.conf"]
