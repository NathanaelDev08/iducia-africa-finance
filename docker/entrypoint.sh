#!/bin/sh
set -e

cd /var/www/html

# Render (et d'autres PaaS) imposent le port d'écoute via $PORT ; Fly.io ne le
# définit pas, on retombe alors sur 8080 (celui déjà déclaré dans fly.toml).
export PORT="${PORT:-8080}"
envsubst '${PORT}' < /etc/nginx/http.d/default.conf.template > /etc/nginx/http.d/default.conf

# S'assurer que les dossiers storage existent
mkdir -p storage/framework/{cache,sessions,views,testing}
mkdir -p storage/logs
mkdir -p storage/app/public
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

if [ "$APP_ENV" = "production" ]; then
    echo "🚀 Démarrage en production..."
    
    # Créer le lien storage si absent
    if [ ! -L public/storage ]; then
        php artisan storage:link 2>/dev/null || true
    fi
    
    # Migrations
    php artisan migrate --force --no-interaction || echo "⚠ Migrations skipped"

    # Compte super admin par défaut (idempotent : ne recrée rien si déjà présent)
    php artisan db:seed --class=Database\\Seeders\\RoleSeeder --force --no-interaction || true
    php artisan db:seed --class=Database\\Seeders\\AdminCompanySeeder --force --no-interaction || true

    # Compte de secours : ne s'active que si ADMIN_RESET_PASSWORD est défini sur Render
    # (à retirer des variables d'environnement une fois utilisé)
    if [ -n "$ADMIN_RESET_PASSWORD" ]; then
        php artisan admin:ensure-password "${ADMIN_RESET_EMAIL:-admin@fiducia-africa.local}" "$ADMIN_RESET_PASSWORD" || true
    fi

    # Caches
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

exec /usr/bin/supervisord -c /etc/supervisord.conf
