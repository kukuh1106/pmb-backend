#!/bin/sh
set -e

echo "🚀 Starting PMB Pascasarjana Backend..."

# Wait for database to be ready
echo "⏳ Waiting for database connection..."
until php artisan db:show --json > /dev/null 2>&1; do
    echo "Database is unavailable - sleeping"
    sleep 2
done
echo "✅ Database is ready!"

# Create storage directories if not exist
echo "📁 Setting up storage directories..."
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/storage/framework/cache
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/app/public
mkdir -p /var/www/html/bootstrap/cache

# Set permissions
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache

# Clear and cache config
echo "🔧 Optimizing Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations if AUTO_MIGRATE is set
if [ "${AUTO_MIGRATE}" = "true" ]; then
    echo "🔄 Running database migrations..."
    php artisan migrate --force
fi

# Create storage link
php artisan storage:link --force 2>/dev/null || true

# Create supervisor log directory
mkdir -p /var/log/supervisor

echo "✅ Initialization complete!"
echo "🌐 Server starting on port 80..."

# Execute the main command
exec "$@"
