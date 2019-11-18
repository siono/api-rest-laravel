<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Post;
use App\Category;

class PruebasController extends Controller
{
    public function index(){
        $titulo = 'animales';
        $animales = ['Perro','Gato','Caballo'];

        return view('pruebas.animales',array(
           'titulo' => $titulo,
           'animales' => $animales
        ));
    }

    public function testOrm(){
        $posts = Post::all();

        foreach ($posts as $post){
            dump($post->category->name);
        }
        die;
    }
}
