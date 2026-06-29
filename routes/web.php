<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/html', function () {
    return '<b>Hello</b>';
});

Route::get('/async-test', function () {
    sleep(5);

    return [];
});
