<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/async-test', function () {
    sleep(5);

    return [];
});
