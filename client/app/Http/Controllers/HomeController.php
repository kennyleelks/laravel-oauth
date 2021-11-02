<?php

namespace App\Http\Controllers;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

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
        // dd($res);

        $user = $request->user();
        $user->access_token = Arr::get($res, 'access_token', '');
        $user->refresh_token = Arr::get($res, 'refresh_token', '');
        $user->save();
        // dd($user);
    
        return redirect('/home');
    }
}
