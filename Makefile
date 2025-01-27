.PHONY: install

install:
	@read -p "Fork (git@github.com:moonshine-software/moonshine.git): " ARG; \
	mkdir -p ./moonshine; \
	git clone $$ARG ./moonshine; \
	cd ./moonshine && \
	composer install --dev && \
	npm install && \
	[ ! -f phpunit.xml.dist ] && cp phpunit-example.xml.dist phpunit.xml.dist && \
	cd .. &&  \
	[ ! -f .env ] && cp .env.example .env && \
	composer update && \
	php artisan migrate:fresh --seed
