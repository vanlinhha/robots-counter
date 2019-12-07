<?php
Route::group(['namespace' => 'LinhHa\RobotsCounter\app\Controllers'], function(){
    Route::get('robots/counter',[
        'as' => 'api.robots.counter',
        'uses' => 'RobotsCounterController@index'
    ]);
});