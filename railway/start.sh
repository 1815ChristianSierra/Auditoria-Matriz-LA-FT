#!/bin/sh
set -eu
: "${APP_KEY:?Configure APP_KEY in Railway; keep it across deployments}"
case "${PORT:-8080}" in *[!0-9]*|'') echo 'Invalid PORT' >&2; exit 1;; esac
sed -i "s/Listen 80/Listen ${PORT:-8080}/" /etc/apache2/ports.conf
sed -i "s/:80>/:${PORT:-8080}>/" /etc/apache2/sites-available/000-default.conf
mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache/data storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
chown -R www-data:www-data storage bootstrap/cache

for mpm in mpm_event mpm_worker; do
  a2dismod "$mpm" >/dev/null 2>&1 || true
  rm -f "/etc/apache2/mods-enabled/${mpm}.load" \
        "/etc/apache2/mods-enabled/${mpm}.conf"
done
a2enmod mpm_prefork
apache2ctl -t

exec apache2-foreground