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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/pruebas/{nombre?}',function($nombre=''){
    $text = '<h2>Pruebas hechas por '.$nombre.'</h2>';
   return view('prueba',array('texto'=>$text));
});

Route::get('/animales','PruebasController@index');

Route::get('/orm','PruebasController@testOrm');

//RUTA API

//Rutas del controlador de usuarios
Route::post('/api/register','UserController@register');
Route::post('/api/login','UserController@login');
