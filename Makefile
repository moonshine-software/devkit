.PHONY: install

install:
	@read -p "Fork (git@github.com:moonshine-software/moonshine.git): " ARG; \
	mkdir -p ./moonshine; \
	git clone $$ARG ./moonshine; \
	cd ./moonshine && \
	composer install --dev && \
	npm install && \
	cd .. &&  \
	[ ! -f .env ] && cp .env.example .env && \
	composer update && \
	sed -i '' 's/\/\///g' bootstrap/providers.php && \
	php artisan migrate:fresh --seed
