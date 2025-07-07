#!/bin/bash

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Function to check if command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Check prerequisites
print_status "Checking prerequisites..."

if ! command_exists docker; then
    print_error "Docker is not installed. Please install Docker first."
    exit 1
fi

if ! command_exists docker-compose; then
    print_error "Docker Compose is not installed. Please install Docker Compose first."
    exit 1
fi

print_success "Prerequisites check passed!"

# Create .env file if it doesn't exist
if [ ! -f .env ]; then
    print_warning ".env file not found. Creating from .env.example..."
    if [ -f .env.example ]; then
        cp .env.example .env
        print_success ".env file created successfully!"
    else
        print_error ".env.example file not found. Please create .env file manually."
        exit 1
    fi
else
    print_status ".env file already exists."
fi

# Stop existing containers (if any)
print_status "Stopping existing containers..."
docker-compose down

# Build and start containers
print_status "Building Docker images..."
docker-compose build

print_status "Starting containers..."
docker-compose up -d

# Wait for containers to be ready
print_status "Waiting for containers to be ready..."
sleep 10

# Set proper permissions immediately after containers start
print_status "Setting proper permissions..."
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
docker-compose exec app chmod -R 775 storage bootstrap/cache

# Check if containers are running
print_status "Checking container status..."
if ! docker-compose ps | grep -q "Up"; then
    print_error "Containers failed to start properly. Please check docker-compose logs."
    docker-compose logs
    exit 1
fi

print_success "Containers are running successfully!"

# Install Composer dependencies
print_status "Installing Composer dependencies..."
docker-compose exec app composer install --no-interaction --prefer-dist --optimize-autoloader

# Generate application key
print_status "Generating application key..."
docker-compose exec app php artisan key:generate

# Clear and cache configuration
print_status "Clearing cache..."
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan view:clear


# Create symbolic link for storage (if it doesn't exist)
print_status "Creating storage symbolic link..."
docker-compose exec app php artisan storage:unlink
docker-compose exec app php artisan storage:link

# Run database migrations
print_status "Running database migrations..."
docker-compose exec app php artisan migrate --force

# Sync products (optional)
print_status "Syncing products..."
docker-compose exec app php artisan sync:products

# Install NPM dependencies and build assets
print_status "Installing NPM dependencies..."
docker-compose exec app npm install

print_status "Building frontend assets..."
docker-compose exec app npm run build


# Display final status
echo ""
print_success "======================================"
print_success "   Application setup completed!"
print_success "======================================"
echo ""
print_status "Application URL: http://localhost:${APP_PORT:-8000}"
print_status "phpMyAdmin URL: http://localhost:${PHPMYADMIN_PORT:-8080}"
echo ""
print_status "Container status:"
docker-compose ps
echo ""
print_status "To view logs: docker-compose logs -f"
print_status "To stop containers: docker-compose down"
print_status "To restart containers: docker-compose restart"
echo ""
print_success "Happy coding! 🚀"
