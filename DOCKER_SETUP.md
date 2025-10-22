# Docker Setup for PHP 8.1 Testing

This guide will help you test the Laravel application with PHP 8.1 using Docker.

## Prerequisites
- Docker installed on your system
- Docker Compose installed on your system

## Quick Start

### Automated Setup (Recommended)

Run the automated setup script:
```bash
./setup.sh
```

This script will:
- Build and start Docker containers
- Update composer dependencies for PHP 8.1
- Generate application key
- Set proper permissions

### Manual Setup

1. **Build and start the containers:**
   ```bash
   docker-compose up -d --build
   ```

2. **Check if containers are running:**
   ```bash
   docker-compose ps
   ```

3. **Update composer dependencies for PHP 8.1 compatibility:**
   ```bash
   docker-compose exec app composer update
   ```

4. **Generate application key:**
   ```bash
   docker-compose exec app php artisan key:generate
   ```

5. **Update .env file:**
   Make sure your database settings point to the Docker database:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=db
   DB_PORT=3306
   DB_DATABASE=domcek
   DB_USERNAME=root
   DB_PASSWORD=root
   ```

6. **Run migrations:**
   ```bash
   docker-compose exec app php artisan migrate
   ```

7. **Access the application:**
   Open your browser and go to: http://localhost:8000

## Useful Commands

### View logs
```bash
# All logs
docker-compose logs -f

# App logs only
docker-compose logs -f app

# Nginx logs only
docker-compose logs -f nginx
```

### Execute commands in the container
```bash
# Access bash shell
docker-compose exec app bash

# Run artisan commands
docker-compose exec app php artisan <command>

# Run tests
docker-compose exec app php artisan test
```

### Stop containers
```bash
docker-compose down
```

### Rebuild containers
```bash
docker-compose down
docker-compose up -d --build
```

## Configuration

### Environment Variables
Make sure to copy `.env.example` to `.env` and update the database settings:

```env
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=domcek
DB_USERNAME=root
DB_PASSWORD=root
```

## Troubleshooting

### Permission Issues
If you encounter permission issues:
```bash
docker-compose exec app chown -R www-data:www-data /var/www/html/storage
docker-compose exec app chmod -R 755 /var/www/html/storage
```

### Clear Cache
```bash
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan view:clear
```

### Check PHP Version
```bash
docker-compose exec app php -v
```

## PHP 8.1 Compatibility Changes

The following critical issues were fixed for PHP 8.1 compatibility:

1. **String concatenation** - Changed `+` to `.` operator in app/Services/ParticipantService.php:269
2. **Null safety** - Added null check in app/Services/ParticipantService.php:214
3. **Array bounds checking** - Added proper array validation in app/Http/Controllers/Secure/PaymentController.php:35

## Notes

- The MySQL database will persist data in a Docker volume named `dbdata`
- The application is accessible via Nginx on port 8000
- PHP-FPM runs on port 9000 (internal)
- MySQL is accessible on port 3306
