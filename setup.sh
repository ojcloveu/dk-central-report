#!/bin/bash

# Setup script for dk-central-report
echo "🚀 Setting up dk-central-report..."

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo "❌ Error: Please run this script from the Laravel project root directory."
    exit 1
fi

# Run migrations
echo "📦 Running migrations..."
php artisan migrate

# Check if migrations were successful
if [ $? -eq 0 ]; then
    echo "✅ Migrations completed successfully!"
else
    echo "❌ Error: Migrations failed!"
    exit 1
fi

# Run seeders
echo "🌱 Running seeders..."
php artisan db:seed --class=ChannelSeeder

# Check if seeders were successful
if [ $? -eq 0 ]; then
    echo "✅ Seeders completed successfully!"
else
    echo "❌ Error: Seeders failed!"
    exit 1
fi

# Clear cache
echo "🧹 Clearing application cache..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear

echo "🎉 Setup completed successfully!"
echo ""
echo "📝 You can now use the following API endpoints:"
echo "   POST /api/sync-bets"
echo "   POST /api/sync-bets/date-range"
echo ""
echo "📖 See docs/BET_SYNC_API.md for detailed documentation."