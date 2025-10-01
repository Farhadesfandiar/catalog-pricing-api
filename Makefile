# run silent
MAKEFLAGS += --silent
SHELL := /usr/bin/bash
ENV ?= dev

.PHONY: composer composer-install composer-update composer-require composer-dump compose-build compose-up compose-down

COMPOSER_IMAGE := composer:2
DOCKER_COMPOSER := docker run --rm -v "$(PWD)":/app -w /app --user $$(id -u):$$(id -g) $(COMPOSER_IMAGE)
IMG_PHP := mythresa-ali/php-fpm:app

# Base composer runner
composer:
	@$(DOCKER_COMPOSER) $(ARGS)

composer-install:
	@$(DOCKER_COMPOSER) install --ignore-platform-reqs

composer-update:
	@$(DOCKER_COMPOSER) update --ignore-platform-reqs

composer-dump:
	@$(DOCKER_COMPOSER) dump-autoload -o

.PHONY: test
test:
	@docker compose exec -e APP_ENV=test -e APP_DEBUG=1 php sh -lc "php -d memory_limit=-1 vendor/bin/phpunit"

compose-build:
	@docker compose build

# build the application and run it with a single command
.PHONY: app-build-up
app-build-up:
	$(MAKE) compose-build
	$(MAKE) up
	$(MAKE) db-wait
	$(MAKE) ENV=dev composer-install
	$(MAKE) ENV=dev migrate
	$(MAKE) ENV=dev seed

warm:
	@docker run --rm \
		-e APP_ENV=$(ENV) -e APP_DEBUG=1 \
		-v ${PWD}:/var/www/html \
		${IMG_PHP} php bin/console cache:warmup --env=$(ENV)

up:
	@docker compose up -d

down:
	@docker compose down

shell:
	@docker compose exec php sh

php-exec:
	@if [ -z "$(CMD)" ]; then echo "Usage: make php-exec CMD=\"your command\""; exit 2; fi
	@docker compose exec php sh -lc "$(CMD)"

# Wait until MySQL accepts connections
.PHONY: db-wait
db-wait:
	@echo "Waiting for MySQL to be ready..."
	@docker compose exec mysql sh -lc 'until mysqladmin ping -h 127.0.0.1 -u root -proot --silent; do sleep 1; done'
	@echo "MySQL is ready."

# Run Doctrine migrations inside php container
.PHONY: migrate
migrate:
	@docker compose exec php sh -lc "php bin/console doctrine:migrations:migrate -n --env=$(ENV)"

# Seed products (idempotent)
.PHONY: seed
seed:
	@docker compose exec php sh -lc "php bin/console app:seed-products --if-empty --env=$(ENV)"


