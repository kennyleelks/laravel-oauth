<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;

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
        $user = $request->user();

        if ($user) {
            // 重整頁面時，隨機更新頭像
            $user->avatar = sprintf("https://avatars.dicebear.com/api/human/%s.svg", Str::random(10));
            $user->save();
        }

        return view('home', ['user' => $user]);
    }
}
