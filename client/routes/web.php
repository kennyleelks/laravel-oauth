<?php

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/redirect', function (Request $request) {
    $query = http_build_query([
        'client_id' => '3',
        'redirect_uri' => 'http://127.0.0.1:8081/callback',
        'response_type' => 'code',
        'scope' => '',
    ]);

    return redirect('http://127.0.0.1:8080/oauth/authorize?'.$query);
});

Route::get('callback', function (Request $request) {
    $response = Http::asForm()->post('http://docker.for.mac.localhost:8080/oauth/token', [
        'grant_type' => 'authorization_code',
        'client_id' => '3',
        'client_secret' => 'H1B9oIGePEfOZtDnExGmxTzIUnmdoy71oM5h4GW0',
        'redirect_uri' => 'http://127.0.0.1:8081/callback',
        'code' => $request->code,
    ]);

    return $response->json();
});

Route::get('refresh', function (Request $request) {
    $response = Http::asForm()->post('http://docker.for.mac.localhost:8080/oauth/token', [
        'grant_type' => 'refresh_token',
        'refresh_token' => $request->token,
        'client_id' => '3',
        'client_secret' => 'H1B9oIGePEfOZtDnExGmxTzIUnmdoy71oM5h4GW0',
        'scope' => '',
    ]);

    return $response->json();
});
