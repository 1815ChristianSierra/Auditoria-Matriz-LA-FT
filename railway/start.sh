
#!/bin/sh
set -eu

: "${APP_KEY:?Configure APP_KEY in Railway; keep it across deployments}"

PORT="${PORT:-8080}"

case "$PORT" in
  ''|*[!0-9]*)
    echo "ERROR: Invalid PORT: $PORT" >&2
    exit 1
    ;;
esac

if [ "$PORT" -lt 1 ] || [ "$PORT" -gt 65535 ]; then
  echo "ERROR: PORT out of range" >&2
  exit 1
fi

# Configurar el puerto de Apache
printf 'Listen %s\n' "$PORT" > /etc/apache2/ports.conf

# Actualizar el VirtualHost
sed -i -E \
  "s/<VirtualHost[[:space:]]+[^>]+>/<VirtualHost *:${PORT}>/" \
  /etc/apache2/sites-available/000-default.conf

# Preparar directorios de Laravel
mkdir -p \
  storage/framework/sessions \
  storage/framework/views \
  storage/framework/cache/data \
  storage/logs \
  bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache

# Optimizar Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache

chown -R www-data:www-data storage bootstrap/cache

# Configurar Apache
for mpm in mpm_event mpm_worker; do
  a2dismod "$mpm" >/dev/null 2>&1 || true
done

a2enmod mpm_prefork

# Verificar configuración
apache2ctl -t

# Iniciar Apache
exec apache2-foreground
