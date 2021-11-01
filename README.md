# laravel-oauth-demo

- Server (Auth Server & Resource Server)
- Client (Resource Owner & Client)

## Server:
- composer require laravel/passport laravel/ui
- php artisan migrate
- php artisan passport:install (初始化 key, 建立兩個測試用的 clients)
- app/Providers/AuthServiceProvider.php 註冊 Passport::routes()
- app/Providers/AuthServiceProvider.php 設定 Token TTL
    - Passport::tokensExpireIn(now()->addDays(15));
    - Passport::refreshTokensExpireIn(now()->addDays(30));
- php artisan passport:client
- php artisan vendor:publish --tag=passport-views
- php artisan ui vue --auth
- npm install && npm run dev
- config/auth.php 新增 api guard

### TODO:
- resource controller

## Client:
- php artisan migrate
- add redirect, callback, refresh route

### TODO:
- get resource with access token