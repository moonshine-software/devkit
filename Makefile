-include .env

THIS_FILE := $(lastword $(MAKEFILE_LIST))
DOCKER_COMPOSE := $(shell command -v docker-compose >/dev/null 2>&1 && echo "docker-compose" || echo "docker compose")

app := $(COMPOSE_PROJECT_NAME)-php
app-npm := npm
path := $(APP_PATH)

.PHONY: install-local install-docker fork build up docker-up info stop it it-app it-nginx npm-install npm-update npm-build npm-host wait-for-app

install-local: fork
	cd ./moonshine && \
	composer install --dev && \
	npm install && \
	[ ! -f phpunit.xml.dist ] && cp phpunit-example.xml.dist phpunit.xml.dist && \
	cd .. &&  \
	[ ! -f .env ] && cp .env.example .env && \
	composer update && \
	touch database/database.sqlite && \
	php artisan migrate:fresh --seed && \
	php artisan serve

install-docker: fork build seed info

up: docker-up info

fork:
	@read -p "Fork (git@github.com:moonshine-software/moonshine.git): " ARG; \
	mkdir -p ./moonshine; \
	git clone $$ARG ./moonshine;
#docker
build:
	$(DOCKER_COMPOSE) -f docker-compose.yml up --build -d $(c)
	@echo "See installation logs: docker logs -f $(app)"
	@echo "Fill the database: make seed"
docker-up:
	$(DOCKER_COMPOSE) -f docker-compose.yml up -d $(c)
stop:
	$(DOCKER_COMPOSE) -f docker-compose.yml stop $(c)
down:
	$(DOCKER_COMPOSE) -f docker-compose.yml down $(c)
it:
	docker exec -it $(to) /bin/bash
it-app:
	docker exec -it $(app) /bin/bash
it-nginx:
	docker exec -it $(nginx) /bin/bash
seed:
	docker exec -it $(app) php artisan db:seed
wait-for-app:
	@echo "⏳ Waiting for migrations and cache..."
	@i=0; \
	while [ $$i -lt 600 ]; do \
		if curl -fsS "$(APP_URL):$(APP_WEB_PORT)/" > /dev/null 2>&1; then \
			echo "✅ App is ready!\n"; exit 0; \
		fi; printf "."; sleep 1; i=$$((i + 1)); \
	done; \
	echo "\n❌ Timeout waiting for app at $(APP_URL):$(APP_WEB_PORT)"; \
	exit 1

info: \
	wait-for-app
	@echo "$(APP_URL):$(APP_WEB_PORT)/admin"
	@echo "User: dev@getmoonshine.app"
	@echo "Pass: 12345"

#npm
npm-install:
	$(DOCKER_COMPOSE) run --rm --service-ports $(app-npm) install $(c)
npm-update:
	$(DOCKER_COMPOSE) run --rm --service-ports $(app-npm) update $(c)
npm-build:
	$(DOCKER_COMPOSE) run --rm --service-ports $(app-npm) run build $(c)
npm-host:
	$(DOCKER_COMPOSE) run --rm --service-ports $(app-npm) run dev --host $(c)
