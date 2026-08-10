#!/bin/sh
set -e

cd /var/www/html

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
    
    # Caches
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

exec /usr/bin/supervisord -c /etc/supervisord.conf
