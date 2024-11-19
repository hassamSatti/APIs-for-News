#!/bin/bash
set -e
# Wait for MySQL to be ready
echo "Waiting for MySQL to be available..."
while ! nc -z db 3306; do
    sleep 1
    echo "Waiting for MySQL to be up..."
done

# Delay to ensure MySQL is fully ready
sleep 10

echo "Running migrations..."
if [ -f artisan ]; then
    php artisan migrate --force || echo "Migrations failed"
    echo "Running seeding..."
    php artisan db:seed --force || echo "Seeding failed"
else
    echo "Could not find artisan. Skipping migrations and seeding."
fi

echo "Starting PHP-FPM..."
exec php-fpm
