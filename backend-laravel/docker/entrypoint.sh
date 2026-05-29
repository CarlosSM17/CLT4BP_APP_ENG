#!/bin/bash
set -e

APP_PORT=${PORT:-8080}

echo "Starting Laravel on port $APP_PORT"

# Adjust Apache to listen on Railway's PORT
sed -i "s/8080/$APP_PORT/g" /etc/apache2/sites-available/000-default.conf
sed -i "s/^Listen 80$/Listen $APP_PORT/" /etc/apache2/ports.conf

# Laravel setup
php artisan migrate --force
php artisan storage:link || true
php artisan config:cache
php artisan route:cache

exec apache2-foreground
