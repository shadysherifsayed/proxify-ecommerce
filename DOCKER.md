# Docker Environment Configuration for ShadeLane

## Overview
This Docker configuration provides a complete development environment for the ShadeLane e-commerce application using:
- **Nginx**: Web server
- **PHP 8.2-FPM**: PHP processor
- **MySQL 8.0**: Database
- **phpMyAdmin**: Database management tool

## Services
- `app`: Main application container (Nginx + PHP-FPM)
- `db`: MySQL database container
- `phpmyadmin`: Database management interface

## Ports
- **8000**: Main application (http://localhost:8000) 
- **8080**: phpMyAdmin (http://localhost:8080)
- **3306**: MySQL database

## Quick Start

1. **Build and start containers:**
   ```bash
   docker-compose up -d --build
   ```

2. **Install dependencies (if needed):**
   ```bash
   docker-compose exec app composer install
   ```

3. **Set up the application:**
   ```bash
   # Copy environment file
   docker-compose exec app cp .env.example .env
   
   # Generate application key
   docker-compose exec app php artisan key:generate
   
   # Run migrations
   docker-compose exec app php artisan migrate
   
   # Sync products
   docker-compose exec app php artisan sync:products
   ```

4. **Access the application:**
   - Application: http://localhost:8000
   - phpMyAdmin: http://localhost:8080

## Useful Commands

### Container Management
```bash
# Start containers
docker-compose up -d

# Stop containers
docker-compose down

# View logs
docker-compose logs -f app

# Rebuild containers
docker-compose up -d --build
```

### Laravel Commands
```bash
# Run artisan commands
docker-compose exec app php artisan migrate
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear

# Install Composer packages
docker-compose exec app composer install

# Run tests
docker-compose exec app php artisan test
```

### Database Access
```bash
# Access MySQL directly
docker-compose exec db mysql -u root -p shadelane

# Import database
docker-compose exec -T db mysql -u root -psecret shadelane < backup.sql
```

## Environment Variables
The following environment variables are configured for Docker:
- `DB_HOST=db`
- `DB_DATABASE=shadelane`
- `DB_USERNAME=root`
- `DB_PASSWORD=secret`

## Volumes
- Application code is mounted to `/var/www/html`
- MySQL data is persisted in the `dbdata` volume
- PHP configuration is mounted from `./docker/php/local.ini`

## Network
All containers are connected via the `shadelane_network` bridge network.

## Troubleshooting

### Permission Issues
```bash
# Fix storage permissions
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
docker-compose exec app chmod -R 775 storage bootstrap/cache
```

### Clear Application Cache
```bash
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan view:clear
```

### Database Connection Issues
- Ensure the database container is running: `docker-compose ps`
- Check database logs: `docker-compose logs db`
- Verify environment variables in `.env` file
