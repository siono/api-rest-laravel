<?php

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

//RUTA API

//Rutas del controlador de usuarios
Route::post('/api/register','UserController@register');
Route::post('/api/login','UserController@login');
Route::put('/api/user/update','UserController@update');
Route::post('/api/user/upload','UserController@upload')->middleware(\App\Http\Middleware\ApiAuthMiddleware::class);
Route::get('/api/user/avatar/{filename}','UserController@getImage');
Route::get('/api/user/detail/{id}','UserController@detail');


// Rutas del controlador de categorias
Route::resource('/api/category','CategoryController');

// Rutas del controlador para entradas

Route::get('/api/post/{id}','PostController@show');
Route::put('/api/post/{id}','PostController@update')->middleware(\App\Http\Middleware\ApiAuthMiddleware::class);
Route::delete('/api/post/{id}','PostController@destroy')->middleware(\App\Http\Middleware\ApiAuthMiddleware::class);
Route::resource('/api/post/','PostController');
Route::post('/api/post/upload','PostController@upload');
Route::get('/api/post/image/{filename}','PostController@getImage');
Route::get('/api/post/category/{id}','PostController@getPostsByCategory');
Route::get('/api/post/user/{id}','PostController@getPostsByUser');



