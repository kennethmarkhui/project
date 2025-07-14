#!/bin/sh

# You can run this to start the project
set -e

echo "🚀 Starting containers..."
docker-compose up -d

echo "⚡ Starting laravel vite dev server..."
docker-compose exec app composer run dev
