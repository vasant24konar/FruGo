# Makefile — convenience wrappers for common tasks
.PHONY: help up down build deploy migrate seed shell logs fresh test lint

COMPOSE = docker compose
APP     = $(COMPOSE) exec app

help: ## Show this help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) \
	  | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}'

up: ## Start all containers
	$(COMPOSE) up -d

down: ## Stop and remove containers
	$(COMPOSE) down

build: ## Rebuild images
	$(COMPOSE) build --no-cache

deploy: ## Full bootstrap (first run)
	bash deploy.sh

migrate: ## Run migrations
	$(APP) php artisan migrate

seed: ## Seed the database
	$(APP) php artisan db:seed

fresh: ## Drop all tables and re-migrate + seed
	$(APP) php artisan migrate:fresh --seed

shell: ## Open a shell inside the app container
	$(COMPOSE) exec app bash

logs: ## Tail application logs
	$(APP) tail -f /var/www/storage/logs/laravel.log

test: ## Run PHPUnit tests
	$(APP) php artisan test

lint: ## Run Laravel Pint (PSR-12 fixer)
	$(APP) ./vendor/bin/pint

cache: ## Cache config, routes, and views
	$(APP) php artisan config:cache
	$(APP) php artisan route:cache
	$(APP) php artisan view:cache

cache-clear: ## Clear all caches
	$(APP) php artisan cache:clear
	$(APP) php artisan config:clear
	$(APP) php artisan route:clear
	$(APP) php artisan view:clear
