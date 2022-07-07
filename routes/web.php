<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;

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
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/auth/redirect', function () {
    return Socialite::driver('twitter-oauth-2')
        ->scopes(['tweet.write', 'offline.access'])
        ->redirect();
});

Route::get('/auth', function () {
    $oAuthUser = Socialite::driver('twitter-oauth-2')->user();
    $user = User::find(auth()->id());
    $user->twitter_token = $oAuthUser->token;
    $user->twitter_refresh_token = $oAuthUser->refreshToken;
    $user->save();

    return redirect()->route('dashboard');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        $user = auth()->user();
        dd(Socialite::driver('twitter-oauth-2')->userFromToken($user->token));
        return Inertia::render('Dashboard');
    })->name('dashboard');
});
