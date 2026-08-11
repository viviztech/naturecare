#!/bin/sh
set -e

cd /var/www/html

# The storage volume is mounted fresh on first boot; recreate the symlink
# and ensure Laravel's writable dirs are owned correctly every start.
php artisan storage:link --force >/dev/null 2>&1 || true
chown -R www-data:www-data storage bootstrap/cache

# Env vars only exist at container runtime (Coolify injects them after the
# image is built), so config/route/view caching has to happen here rather
# than in the Dockerfile - caching at build time would bake in empty values.
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

exec "$@"
