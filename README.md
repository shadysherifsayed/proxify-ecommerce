# Shadelane - E-commerce Platform

A modern e-commerce platform built with Laravel and Vue.js, featuring cart management, product catalog, and order processing.

## � Tech Stack

### Backend
- **PHP 8.2+** - Server-side language
- **Laravel 12** - PHP framework
- **MySQL** - Primary database
- **Redis** - Caching and session storage
- **Laravel Telescope** - Application debugging
- **Laravel Pulse** - Application monitoring
- **Inertia.js** - Server-side rendering

### Frontend
- **Vue.js 3** - Frontend framework
- **TypeScript** - Type safety
- **Vite** - Build tool and dev server
- **Sass** - CSS preprocessor
- **ESLint & Prettier** - Code formatting and linting

### DevOps & Tools
- **Docker & Docker Compose** - Containerization
- **Nginx** - Web server
- **phpMyAdmin** - Database management
- **Pest** - PHP testing framework
- **Vitest** - JavaScript testing framework

## 📋 Prerequisites

Before setting up the application, ensure you have the following installed:

- **Docker** (v20.10+)
- **Docker Compose** (v2.0+)
- **Git**

## 🛠️ Quick Setup (Automated)

The easiest way to set up the application is using the provided setup script:

```bash
# Clone the repository
git clone `https://github.com/shadysherifsayed/proxify-ecommerce.git`
cd proxify-ecommerce

# Make setup script executable
chmod +x setup.sh

# Run the setup script
./setup.sh
```

The setup script will:
- ✅ Check prerequisites
- ✅ Create `.env` file from `.env.example`
- ✅ Build and start Docker containers
- ✅ Set proper file permissions
- ✅ Install Composer dependencies
- ✅ Generate application key
- ✅ Clear and optimize caches
- ✅ Run database migrations
- ✅ Sync products from external API
- ✅ Install NPM dependencies and build assets

## � Manual Setup

If you prefer to set up the application manually, follow these steps:

### 1. Clone and Configure

```bash
# Clone the repository
git clone <repository-url>
cd shadelane

# Copy environment file
cp .env.example .env
```

### 2. Start Docker Containers

```bash
# Build and start containers
docker-compose build
docker-compose up -d
```

### 3. Set Permissions

```bash
# Set proper file permissions
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
docker-compose exec app chmod -R 775 storage bootstrap/cache
```

### 4. Install Dependencies

```bash
# Install PHP dependencies
docker-compose exec app composer install --no-interaction --prefer-dist --optimize-autoloader

# Install Node.js dependencies
docker-compose exec app npm install
```

### 5. Configure Application

```bash
# Generate application key
docker-compose exec app php artisan key:generate

# Clear caches
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan view:clear

# Create storage symbolic link
docker-compose exec app php artisan storage:link
```

### 6. Database Setup

```bash
# Run migrations
docker-compose exec app php artisan migrate --force

# Sync products from external API (optional)
docker-compose exec app php artisan sync:products
```

### 7. Build Frontend Assets

```bash
# Build for production
docker-compose exec app npm run build

# Or run in development mode
docker-compose exec app npm run dev
```

## 🌐 Access Points

After successful setup, you can access:

- **Application**: http://localhost:8000
- **phpMyAdmin**: http://localhost:8080
- **Vite Dev Server**: http://localhost:5174 (development only)

## 🚨 Common Issues & Troubleshooting

### Permission Issues

**Problem**: Permission denied errors for storage or cache directories.

**Solution**:
```bash
# Fix permissions
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
docker-compose exec app chmod -R 775 storage bootstrap/cache

# If still having issues, try:
sudo chown -R $USER:$USER storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

### Vite Building Issues

**Problem**: Frontend asset are not built successfully 

**Solution**:
```bash
# Fix the build issue
docker-compose exec app npm run build

# If still having issues, try running the app in dev mode:
docker-compose exec app npm run dev
```

### Database Connection Issues

**Problem**: Cannot connect to database.

**Solutions**:
1. Check if database container is running:
   ```bash
   docker-compose ps
   ```

2. Verify database environment variables in `.env`:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=db
   DB_PORT=3306
   DB_DATABASE=shadelane
   DB_USERNAME=root
   DB_PASSWORD=password
   ```

3. Restart containers:
   ```bash
   docker-compose restart
   ```

### Port Conflicts

**Problem**: Ports already in use.

**Solution**: Update ports in `.env` file:
```env
APP_PORT=8001
VITE_PORT=5175
PHPMYADMIN_PORT=8081
```

### Container Build Issues

**Problem**: Docker build fails.

**Solutions**:
1. Clean Docker system:
   ```bash
   docker system prune -a
   docker-compose down --volumes --remove-orphans
   ```

2. Rebuild containers:
   ```bash
   docker-compose build --no-cache
   docker-compose up -d
   ```

### Missing Dependencies

**Problem**: Composer or NPM dependencies not installed.

**Solution**:
```bash
# Reinstall PHP dependencies
docker-compose exec app composer install --no-interaction

# Reinstall Node.js dependencies
docker-compose exec app npm install

# Clear composer cache if needed
docker-compose exec app composer clear-cache
```

### Memory Issues

**Problem**: Out of memory errors.

**Solution**: Increase Docker memory allocation in Docker Desktop settings or optimize Composer:
```bash
docker-compose exec app composer install --no-dev --optimize-autoloader
```

## 🧪 Testing

### PHP Tests (Pest)
```bash
# Run all PHP tests
docker-compose exec app php artisan test

# Run specific test file
docker-compose exec app php artisan test tests/Feature/CartTest.php
```

### JavaScript Tests (Vitest)
```bash
# Run JavaScript tests
docker-compose exec app npm run test
```

## 🛠️ Development Commands

### Laravel Artisan Commands
```bash
# Sync products from external API
docker-compose exec app php artisan sync:products

# Run any artisan command
docker-compose exec app php artisan <command>
```

### Frontend Development

```bash
# Build and compile all assets
docker-compose exec app npm run build

# Run the vue app in dev mode
docker-compose exec app npm run dev
```

### 4. Access the Application

- **Main Application**: http://localhost:8000
- **PHPMyAdmin**: http://localhost:8080
- **Vite Dev Server**: http://localhost:5174 (for hot reloading)

#### PHPMyAdmin Login:
- **Username**: `root` or `shadelane`
- **Password**: `secret`

### Running Tests

```bash
# PHP Tests (Pest)
docker-compose exec app php artisan test

# JavaScript Tests (Vitest)
docker-compose exec app npm test

# Run specific test suite
docker-compose exec app php artisan test --filter=ProductController
```

## 📦 Docker Services

The application runs the following services:

- **app**: Main Laravel application (PHP-FPM + Nginx)
- **db**: MySQL database
- **redis**: Redis cache server
- **phpmyadmin**: Database management interface

## 🔄 Docker Management

```bash
# View container status
docker-compose ps

# View logs
docker-compose logs -f

# View specific service logs
docker-compose logs -f app

# Stop containers
docker-compose down

# Stop and remove volumes
docker-compose down --volumes

# Restart containers
docker-compose restart

# Access container shell
docker-compose exec app bash
```
