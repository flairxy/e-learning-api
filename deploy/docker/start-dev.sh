#!/usr/bin/env bash

#composer install && composer dump-autoload -o
php artisan migrate:fresh --seed --force

