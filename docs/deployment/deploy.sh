#!/usr/bin/env bash
# deploy.sh — bootstrap and deploy the Product Manager application
set -euo pipefail

###############################################################################
# Helpers
###############################################################################
GREEN='\033[0;32m'; YELLOW='\033[1;33m'; RED='\033[0;31m'; NC='\033[0m'
info()    { echo -e "${GREEN}[INFO]${NC}  $*"; }
warn()    { echo -e "${YELLOW}[WARN]${NC}  $*"; }
error()   { echo -e "${RED}[ERROR]${NC} $*" >&2; exit 1; }

###############################################################################
# Prerequisite checks
###############################################################################
info "Checking prerequisites…"
command -v docker  >/dev/null 2>&1 || error "Docker is not installed."
command -v docker compose >/dev/null 2>&1 || \
  command -v docker-compose >/dev/null 2>&1 || error "Docker Compose is not installed."

# Normalize compose command
COMPOSE="docker compose"
docker compose version >/dev/null 2>&1 || COMPOSE="docker-compose"

###############################################################################
# Environment configuration
###############################################################################
if [ ! -f .env ]; then
    info "Creating .env from .env.example…"
    cp .env.example .env
    warn "Please review .env and update credentials before running in production."
fi

###############################################################################
# Build & start containers
###############################################################################
info "Building and starting containers…"
$COMPOSE up -d --build

###############################################################################
# Wait for MySQL to be healthy
###############################################################################
info "Waiting for database to be ready…"
MAX_ATTEMPTS=30
ATTEMPT=0
until $COMPOSE exec -T db mysqladmin ping -h localhost --silent 2>/dev/null; do
    ATTEMPT=$((ATTEMPT + 1))
    if [ "$ATTEMPT" -ge "$MAX_ATTEMPTS" ]; then
        error "Database did not become ready in time."
    fi
    echo -n "."
    sleep 2
done
echo ""
info "Database is ready."

###############################################################################
# Application setup inside the app container
###############################################################################
info "Installing Composer dependencies…"
$COMPOSE exec -T app composer install --no-dev --optimize-autoloader --no-interaction

info "Generating application key…"
$COMPOSE exec -T app php artisan key:generate --force

info "Running database migrations…"
$COMPOSE exec -T app php artisan migrate --force

info "Seeding database…"
$COMPOSE exec -T app php artisan db:seed --force

info "Caching configuration and routes…"
$COMPOSE exec -T app php artisan config:cache
$COMPOSE exec -T app php artisan route:cache
$COMPOSE exec -T app php artisan view:cache

info "Creating upload directories…"
$COMPOSE exec -T app mkdir -p /var/www/public/img/products

info "Setting storage permissions…"
$COMPOSE exec -T app chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache /var/www/public/img/products

###############################################################################
# Done
###############################################################################
echo ""
info "Deployment complete."
info "Application is available at: http://localhost:8080"
info "MailHog UI (dev emails): http://localhost:8025"
info ""
info "Demo accounts:"
info "  Admin:   admin@example.com   / Admin@1234"
info "  Manager: manager@example.com / Manager@1234"
