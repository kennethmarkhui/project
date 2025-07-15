#!/bin/sh

# Run only for the first time to setup docker
set -e

echo "📄 Copying .env"
cp .env.example .env

export HOST_UID=$(id -u)
export HOST_GID=$(id -g)

echo "🐳 Building Docker containers..."
docker-compose build --build-arg HOST_UID=$HOST_UID --build-arg HOST_GID=$HOST_GID

echo "🚀 Starting containers..."
docker-compose up -d

echo "🎼 Running Composer install..."
docker-compose exec app composer install --ignore-platform-reqs

echo "🔑 Generating Laravel app key..."
docker-compose exec app php artisan key:generate

echo "📦 Installing Node packages and building assets..."
docker-compose exec app npm ci
docker-compose exec app npm run build

echo "🛠 Running migrations and seeders..."
docker-compose exec app php artisan migrate:fresh --seed

echo "🛑 Closing containers..."
docker-compose down

echo "✅ Setup complete!"