1) composer install
Для тестирования будем использовать sqlite, файл storage/app/database.sqlite
2) cp(или copy если используется windows) .env.example.sqlite .env
3) php artisan key:generate --ansi
4) php artisan app:create_sqlite_database

Контроллер всех действий находится в app/Http/Controllers/GoodsController.php
Роутинг routes/api.php
Тесты tests/Feature/GoodsTest.php

Команда запуска тестов composer exec phpunit -v
