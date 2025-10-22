#!/bin/bash

# Setup script for PHP 8.1 Laravel project

echo "==================================="
echo "Laravel PHP 8.1 Setup Script"
echo "==================================="
echo ""

# Step 1: Build and start containers
echo "Step 1: Building and starting Docker containers..."
docker-compose up -d --build
if [ $? -ne 0 ]; then
    echo "ERROR: Failed to build containers"
    exit 1
fi
echo "✓ Containers started successfully"
echo ""

# Step 2: Wait for containers to be ready
echo "Step 2: Waiting for containers to be ready..."
sleep 5
echo "✓ Containers are ready"
echo ""

# Step 3: Copy .env.example to .env if needed
echo "Step 3: Setting up environment file..."
docker-compose exec -T app bash -c "if [ ! -f .env ]; then cp .env.example .env; fi"
echo "✓ Environment file ready"
echo ""

# Step 4: Update composer dependencies for PHP 8.1
echo "Step 4: Updating composer dependencies for PHP 8.1 compatibility..."
echo "This may take a few minutes..."
docker-compose exec -T app composer update --no-interaction
if [ $? -ne 0 ]; then
    echo "WARNING: Composer update had issues. You may need to run it manually."
else
    echo "✓ Composer dependencies updated"
fi
echo ""

# Step 5: Generate application key
echo "Step 5: Generating application key..."
docker-compose exec -T app php artisan key:generate --force
echo "✓ Application key generated"
echo ""

# Step 6: Set permissions
echo "Step 6: Setting permissions..."
docker-compose exec -T app chmod -R 775 storage bootstrap/cache
docker-compose exec -T app chown -R www-data:www-data storage bootstrap/cache
echo "✓ Permissions set"
echo ""

# Step 7: Check PHP version
echo "Step 7: Verifying PHP version..."
docker-compose exec -T app php -v
echo ""

echo "==================================="
echo "Setup Complete!"
echo "==================================="
echo ""
echo "Next steps:"
echo "1. Update your .env file with correct database credentials:"
echo "   DB_HOST=db"
echo "   DB_DATABASE=domcek"
echo "   DB_USERNAME=root"
echo "   DB_PASSWORD=root"
echo ""
echo "2. Run migrations:"
echo "   docker-compose exec app php artisan migrate"
echo ""
echo "3. Access your application at: http://localhost:8000"
echo ""
echo "Useful commands:"
echo "  docker-compose logs -f        # View logs"
echo "  docker-compose exec app bash  # Access container shell"
echo "  docker-compose down           # Stop containers"
echo ""
