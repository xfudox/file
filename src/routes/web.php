<?php

use Illuminate\Support\Facades\Route;

    Route::group(['prefix' => 'files', 'namespace' => '\xfudox\File\Http\Controllers', 'as'=>'files.'],
        function(){
            Route::get('/', 'FileController@index')->name('index');
            Route::post('upload', 'FileController@upload')->name('upload');
        });
