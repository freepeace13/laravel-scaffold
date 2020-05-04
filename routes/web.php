<?php

use Illuminate\Support\Facades\Route;

Route::get('/home', 'HomeController@index')->name('home');

Route::group(['prefix' => 'admin'], function () {
    Voyager::routes();
});

Auth::routes();
