#!/bin/sh
set -e

cd moonshine && composer install

cd ../ && composer install

php artisan migrate --force
php artisan db:seed
php artisan optimize:clear

php artisan vendor:publish --tag=laravel-assets --force

exec "$@"