#!/usr/bin/env bash

# Local test runner for development
# Run tests without coverage for faster feedback

set -e

echo "🧪 Running Tests (Local Development)..."

# Check if vendor directory exists
if [ ! -d "vendor" ]; then
    echo "📦 Installing dependencies..."
    composer install --no-progress --prefer-dist
fi

# Copy environment file if it doesn't exist
if [ ! -f ".env" ]; then
    cp .env.example .env
    php artisan key:generate
fi

# Run tests without coverage for faster feedback
vendor/bin/phpunit \
    --colors=always \
    --testdox \
    --verbose

echo "✅ All tests passed!"