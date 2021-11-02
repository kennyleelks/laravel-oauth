# laravel-oauth-demo

這個 demo 是利用兩個 Laravel 專案來示範 Oauth 2.0 流程。

兩個專案分別為：
- server (做為 Auth Server & Resource Server)
- client (做為 Resource Owner & Client)

## Step 1: 修改 Server

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
1. 修改 database/migrations/xxxx_xx_xx_xxxxxx_create_users_table.php
    ```php
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->text('avatar'); // 新增: 頭像網址
            $table->timestamps();
        });
    }
    ```
1. 重建資料表
    ```console
    $ php artisan migrate:refresh
    ```
1. 修改 app/Models/User.php
    ```php
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password',

        // 使這個欄位可以被寫入
        'avatar',
    ];
    ```
1. 修改 app/Http/Controllers/Auth/RegisterController.php
    ```php
    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),

            // 註冊用戶時，隨機產生頭像
            'avatar' => sprintf("https://avatars.dicebear.com/api/human/%s.svg", Str::random(10)),
        ]);
    }
    ```
1. 修改 app/Http/Controllers/HomeController.php
    ```php
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user) {
            // 重整頁面時，隨機更新頭像
            $user->avatar = sprintf("https://avatars.dicebear.com/api/human/%s.svg", Str::random(10));
            $user->save();
        }

        return view('home', ['user' => $user]);
    }
    ```
1. 修改 resources/views/home.blade.php
    ```html
    <div class="card">
        <div class="card-header">{{ $user->name }}'s avatar</div>

        <div class="card-body">
            <img class="img-fluid" src="{{ $user->avatar }}" alt="">
        </div>
    </div>
    ```
1. 修改 routes/api.php
    ```php
    Route::middleware('auth:api')->get('/user', function (Request $request) {
        return $request->user(); // 取得當前登入的 User 資料後以 JSON 輸出
    });
    ```

## Step 2: 修改 Client

1. 確認已經安裝 Laravel UI，用來產生預設登入、註冊頁面
    ```console
    $ composer require laravel/ui
    $ php artisan ui vue --auth
    $ npm install && npm run dev
    ```
1. 修改 database/migrations/xxxx_xx_xx_xxxxxx_create_users_table.php
    ```php
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->text('access_token');   // 新增: Access token
            $table->text('refresh_token');  // 新增: Refresh token
            $table->text('avatar');         // 新增: 頭像網址
            $table->timestamps();
        });
    }
    ```
1. 建立所需資料表
    ```console
    $ php artisan migrate
    ```
1. 修改 .env
    ```env
    OAUTH_API_URL=http://host.docker.internal:8000/api
    OAUTH_AUTHORIZE_URL=http://127.0.0.1:8000/oauth/authorize
    OAUTH_CALLBACK_URL=http://127.0.0.1:8001/callback
    OAUTH_GET_TOKEN_URL=http://host.docker.internal:8000/oauth/token
    OAUTH_CLIENT_ID=1                                               # 填入建立 client 時回傳的 ClientID
    OAUTH_CLIENT_SECRET=5XBUxR0CoStBFrOkHHcaARSoI15DvJJmxGVfrTPq    # 填入建立 client 時回傳的 Secret
    ```
1. 修改 app/Models/User.php
    ```php
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password',

        // 使這些欄位可以被寫入
        'access_token',
        'refresh_token',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',

        // 避免 token 意外洩漏
        'access_token',
        'refresh_token',
    ];
    ```
1. 修改 app/Http/Controllers/Auth/RegisterController.php
    ```php
    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),

            // 註冊用戶時，填入空值
            'access_token' => '',
            'refresh_token' => '',
            'avatar' => '',
        ]);
    }
    ```
1. 修改 app/Http/Controllers/HomeController.php
    ```php
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        return view('home', ['user' => $request->user()]);
    }

    /**
     * 更新頭像
     */
    public function avatar(Request $request)
    {
        $user = $request->user();
        if ($user && !empty($user->access_token)) {
            $response = Http::withHeaders([
                'Authorization' => sprintf('Bearer %s', $user->access_token),
            ])->get(env('OAUTH_API_URL') . '/user');

            $res = $response->json();
            $user->avatar = Arr::get($res, 'avatar', '');
            $user->save();
        }

        return redirect('/home');
    }

    /**
     * 收到 Oauth server 的 callback 之後
     * 更新 User 的 token 欄位
     */
    public function callback(Request $request)
    {
        $response = Http::asForm()->post(env('OAUTH_GET_TOKEN_URL'), [
            'grant_type' => 'authorization_code',
            'client_id' => env('OAUTH_CLIENT_ID'),
            'client_secret' => env('OAUTH_CLIENT_SECRET'),
            'redirect_uri' => env('OAUTH_CALLBACK_URL'),
            'code' => $request->code,
        ]);

        $res = $response->json();

        $user = $request->user();
        $user->access_token = Arr::get($res, 'access_token', '');
        $user->refresh_token = Arr::get($res, 'refresh_token', '');
        $user->save();

        return redirect('/home');
    }
    ```
1. 修改 resources/views/home.blade.php
    ```html
    <div class="card">
        <div class="card-header">{{ $user->name }}'s avatar</div>

        <div class="card-body">
            @if (empty($user->access_token))
                <a href="{{ url('redirect') }}" class="btn btn-primary">第三方登入</a>
            @else
                <a href="{{ url('avatar') }}" class="btn btn-primary">更新頭像</a>
            @endif
            <hr>
            @if (!empty($user->avatar))
                <img class="img-fluid" src="{{ $user->avatar }}" alt="{{ $user->name }}">
            @endif
        </div>
    </div>
    ```
1. 修改 routes/web.php
    ```php
    Route::get('/redirect', function (Request $request) {
        $query = http_build_query([
            'client_id' => env('OAUTH_CLIENT_ID'),
            'redirect_uri' => env('OAUTH_CALLBACK_URL'),
            'response_type' => 'code',
            'scope' => '',
        ]);

        return redirect(env('OAUTH_AUTHORIZE_URL') . '?' . $query);
    });
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::get('/avatar', [App\Http\Controllers\HomeController::class, 'avatar'])->name('avatar');
    Route::get('/callback', [App\Http\Controllers\HomeController::class, 'callback'])->name('callback');
    ```
