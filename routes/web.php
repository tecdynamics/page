<?php

use Tec\Base\Facades\BaseHelper;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Tec\Page\Http\Controllers', 'middleware' => ['web', 'core']], function () {
    Route::group(['prefix' => BaseHelper::getAdminPrefix(), 'middleware' => 'auth'], function () {
        Route::group(['prefix' => 'pages', 'as' => 'pages.'], function () {
            Route::resource('', 'PageController')->parameters(['' => 'page']);

        Route::get('duplicate/{key}', [
            'as' => 'duplicatepage',
            'uses' => 'PageController@DuplicatePage',
        ]);
    });
    });
});
