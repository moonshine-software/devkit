<?php

use App\Http\Middleware\TelegramAuth;
use Illuminate\Support\Facades\Route;
use MoonShine\Contracts\Core\DependencyInjection\RouterContract;

Route::get('/', function () {
    return view('welcome');
});

Route::moonshine(static function () {
    Route::view('/telegram-startapp', 'telegram-startapp')
        ->name('telegram-startapp');

    Route::middleware(TelegramAuth::class)->post('/telegram-login', function (RouterContract $router) {
        return response()->json([
            'url' => $router->getEndpoints()->home(),
        ]);
    })->name('telegram-login');
});



Route::get('/async-test', function () {
    sleep(5);

    return [];
});
