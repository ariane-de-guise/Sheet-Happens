vendor/bin/sail down -v
vendor/bin/sail up -d
vendor/bin/sail artisan migrate:fresh --seed
