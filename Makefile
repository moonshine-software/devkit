-include .env

THIS_FILE := $(lastword $(MAKEFILE_LIST))

app := $(COMPOSE_PROJECT_NAME)-php
app-npm := npm
path := $(APP_PATH)

.PHONY: install-local install-docker fork build up docker-up info stop it it-app it-nginx npm-install npm-update npm-build npm-host

install-local: fork
	cd ./moonshine && \
	composer install --dev && \
	npm install && \
	[ ! -f phpunit.xml.dist ] && cp phpunit-example.xml.dist phpunit.xml.dist && \
	cd .. &&  \
	[ ! -f .env ] && cp .env.example .env && \
	composer update && \
	php artisan migrate:fresh --seed

install-docker: fork build info

up: docker-up info

fork:
	@read -p "Fork (git@github.com:moonshine-software/moonshine.git): " ARG; \
	mkdir -p ./moonshine; \
	git clone $$ARG ./moonshine;
#docker
build:
	docker-compose -f docker-compose.yml up --build -d $(c)
	@echo "See installation logs: docker logs -f $(app)"
	@echo "Fill the database: make seed"
docker-up:
	docker-compose -f docker-compose.yml up -d $(c)
stop:
	docker-compose -f docker-compose.yml stop $(c)
it:
	docker exec -it $(to) /bin/bash
it-app:
	docker exec -it $(app) /bin/bash
it-nginx:
	docker exec -it $(nginx) /bin/bash
seed:
	docker exec -it $(app) php artisan db:seed

info:
	@echo "$(APP_URL)/admin"
	@echo "User: dev@getmoonshine.app"
	@echo "Pass: 12345"

#npm
npm-install:
	docker-compose run --rm --service-ports $(app-npm) install $(c)
npm-update:
	docker-compose run --rm --service-ports $(app-npm) update $(c)
npm-build:
	docker-compose run --rm --service-ports $(app-npm) run build $(c)
npm-host:
	docker-compose run --rm --service-ports $(app-npm) run dev --host $(c)