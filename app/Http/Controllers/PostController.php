<?php

namespace App\Http\Controllers;

use App\Post;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Helpers\JwtAuth;

class PostController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('api.auth',['except'=> ['index','show','getImage','getPostsByCategory','getPostsByUser']]);
    }

    public function index()
    {
        $posts = Post::all()->load('Category')->load('User');

        return response()->json(
            [
                'code' => 200,
                'status' => 'sucess',
                'posts' => $posts,
            ]
        );
    }

    public function show($id)
    {
        $post = Post::find($id);

        if (is_object($post)){
            $post->load('category');
            $data = [
                'code' => 200,
                'status' => 'sucess',
                'post' => $post,
            ];
        }else{
            $data = [
                'code' => 404,
                'status' => 'error',
                'post' => 'La entrada no existe',
            ];
        }
        return response()->json($data,$data['code']);
    }


    public function store(Request $request)
    {
        // Recoger los datos por post
        $json = $request->input('json',null);
        $param_array = json_decode($json,true);

        if (!empty($param_array)){

            //conseguir los datos del usuario
            $user = $this->getIdentity($request);

            //Validar los datos
            $validate = Validator::make($param_array,[
                'title' => 'required',
                'content' => 'required',
                'category_id' => 'required'
            ]);

            if ($validate->fails()){
                $data = [
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'No se ha guardado el post'
                ];
            }else{
                $post = new Post();
                $post->user_id = $user->sub;
                $post->category_id = $param_array['category_id'];
                $post->title = $param_array['title'];
                $post->content = $param_array['content'];
                $post->image = $param_array['image']??null;
                $post->save();

                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'post' => $post
                ];
            }
        }else{
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'Debe pasar title,content y category_id para crear el post'
            ];
        }


        return response()->json($data, $data['code']);
    }


    public function update($id, Request $request){

        // Recoger los datos por post
        $json = $request->input('json',null);
        $param_array = json_decode($json,true);



        if (!empty($param_array)){
            // Validar los datos
            $validate = Validator::make($param_array, [
                'title' => 'required',
                'category_id' => 'required',
                'content' => 'required',
            ]);

            if ($validate->fails())
            {
                return $request->json($validate->errors(),400);
            }
            // Quitar lo que no quiero actualizar
            unset(
                $param_array['id'],
                $param_array['user_id'],
                $param_array['created_at']
            );

            $user = $this->getIdentity($request);
            // Actualizar el regitro(categoria)
            $post = Post::where('id',$id)
                        ->where('user_id',$user->sub)
                        ->update($param_array);

            if ($post){
                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'post' => $param_array
                ];
            }else{
                $data = [
                    'code' => 400,
                    'status' => 'error',
                    'error' => 'No existe post'
                ];
            }
        }else{
            $data = [
                'code' => 400,
                'status' => 'error',
                'error' => 'Debe pasar title,content y category_id para actualizar el post'
            ];
        }
        // Devolver respuesta
        return response()->json($data, $data['code']);
    }


    public function destroy($id, Request $request){

        //conseguir los datos del usuario
        $user = $this->getIdentity($request);

        $post = Post::where('id',$id)
                    ->where('user_id',$user->sub)
                    ->first();

        if (is_object($post)){

            $post->delete();

            $post->load('category');
            $data = [
                'code' => 200,
                'status' => 'sucess',
                'post' => 'Se ha eliminado el post:'.$post,
            ];
        }else{
            $data = [
                'code' => 404,
                'status' => 'error',
                'post' => 'La entrada no existe',
            ];
        }
        return response()->json($data,$data['code']);
    }


    private function getIdentity(Request $request){
        //conseguir los datos del usuario
        $jwtAuth = new JwtAuth();
        $token = $request->header('Authorization',null);
        $user = $jwtAuth->checkToken($token,true);
        return $user;
    }


    public function upload(Request $request){

        //Recoger la imagen de la peticion
        $image = $request->file('file0');

        //Validar la imagen
        $validator = Validator::make($request->all(),[
            'file0' => 'required|image|mimes:jpg,jpeg,png,gif'
        ]);

        //Guardar la imagen
        if (!$image || $validator->fails()){
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'Error al subir la imagen'
            ];
        }else{
            $image_name = time().$image->getClientOriginalName();
            //guardamos en disco.
            Storage::disk('images')->put($image_name, File::get($image));

            $data = [
                'code' => 200,
                'status' => 'success',
                'image' => $image_name
            ];
        }

        //Devolver datos
        return \response()->json($data,$data['code']);
    }


    public function getImage($filename){

        //comprobar si esxiste el fichero
        $isset = Storage::disk('images')->exists($filename);

        if ($isset){
            //conseguir imagen
            $file = Storage::disk('images')->get($filename);

            //devolver la imagen
            return new Response($file,200);
        }else{
            $data = [
              'code' => 400,
              'status' => 'error',
              'message' => 'La imagen no existe'
            ];
        }
        return \response()->json($data,$data['code']);
    }


    public function getPostsByCategory($id){
        $posts = Post::where('category_id', $id)->get();

        return \response()->json([
            'status'=> 'sucess',
            'posts' => $posts
        ],200);

    }

    public function getPostsByUser($id){
        $posts = Post::where('user_id', $id)->get();

        return \response()->json([
            'status'=> 'sucess',
            'posts' => $posts
        ],200);

    }

}
