# laravel-oauth-demo

這個 demo 是利用兩個 Laravel 專案來示範 Oauth 2.0 流程。

兩個專案分別為：
- server (做為 Auth Server & Resource Server)
- client (做為 Resource Owner & Client)

## Step 1: 初始化 Server

1. 確認已經安裝 Laravel UI，用來產生預設登入、註冊頁面
    ```console
    $ composer require laravel/ui
    $ php artisan ui vue --auth
    $ npm install && npm run dev
    ```
1. 確認已經安裝 Laravel Passport
    ```console
    $ composer require laravel/passport
    ```
1. 建立所需資料表
    ```console
    $ php artisan migrate
    ```
1. 初始化 Laravel Passport (建立 key, 建立兩個測試用的 clients)
    ```console
    $ php artisan passport:install
    ```
1. config/auth.php
    ```php
    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        // 新增 api guard, driver 設為 passport
        'api' => [
            'driver' => 'passport',
            'provider' => 'users',
        ],
    ],
    ```
1. app/Providers/AuthServiceProvider.php
    ```php
    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Passport::routes();                                  // 註冊 Passport 相關 Routes
        Passport::tokensExpireIn(now()->addDays(15));        // 設置 Access Token 過期時間
        Passport::refreshTokensExpireIn(now()->addDays(30)); // 設置 Refresh Token 過期時間
    }
    ```
1. 註冊新 Client
    ```console
    $ php artisan passport:client
    
    Which user ID should the client be assigned to?:            // 用來註記這個 Client 是哪個 User 產生的(不重要)，留空即可 
    $
    
    What should we name the client?:                            // Client 的顯示名稱，隨意取
    $ Demo APP

                                                                // 重要!!! 當用戶按下同意授權後要跳轉到 Client 的哪個頁面？
    Where should we redirect the request after authorization?:  // 後續請求帶上的 redirect_url 都要跟這邊設定的值一樣!
    $ http://client.test/callback

                                                                // Client 產生完成後會回傳 Client ID 跟 Secret
    New client created successfully.                            // 務必妥善保存，後續請求的時候會用到
    Client ID: 1
    Client secret: 5XBUxR0CoStBFrOkHHcaARSoI15DvJJmxGVfrTPq
    ```

## Step 2: 初始化 Client

1. 建立所需資料表
    ```console
    $ php artisan migrate
    ```
