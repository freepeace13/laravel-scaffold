<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Auth'], function () {
    Route::post('/login', 'LoginController@login')->middleware('guest:sanctum');
    Route::get('/logout', 'LoginController@logout')->middleware('auth:sanctum');
});

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
