#!/bin/sh

cd ../
npm install
npm run build-dev
composer install
bin/console doctrine:migrations:migrate --no-interaction
cd public/
php-fpm
