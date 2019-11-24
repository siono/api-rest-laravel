<?php

namespace App\Http\Controllers;

use App\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('api.auth',['except'=> ['index','show']]);
    }

    public function index()
    {
        $categories = Category::all();

        return response()->json(
            [
                'code' => 200,
                'status' => 'sucess',
                'categories' => $categories,
            ]
        );
    }

    public function show($id)
    {
        $category = Category::find($id);
        if (is_object($category)) {
            $data = [
                'code' => 200,
                'status' => 'sucess',
                'categories' => $category,
            ];
        } else {
            $data = [
                'code' => 404,
                'status' => 'error',
                'categories' => 'La categoria no existe',
            ];
        }

        return response()->json($data, $data['code']);
    }

    public function store(Request $request)
    {
        // Recoger los datos por post
        $json = $request->input('json',null);
        $param_array = json_decode($json,true);

        if (!empty($param_array)){

        //Validar los datos
        $validate = Validator::make($param_array,[
           'name' => 'required'
        ]);

        if ($validate->fails()){
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'No se ha guardado la categoria'
            ];
        }else{
            $category = new Category();
            $category->name = $param_array['name'];
            $category->save();

            $data = [
                'code' => 200,
                'status' => 'success',
                'category' => $category
            ];
        }
        }else{
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'Debe pasar el nombre de la categoría'
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
                'name' => 'required'
            ]);
        // Quitar lo que no quiero actualizar
            unset($param_array['id'],$param_array['created_at']);

        // Actualizar el regitro(categoria)
            $category = Category::where('id',$id)->update($param_array);

            if ($category){
                $data = [
                    'code' => 200,
                    'status' => 'success',
                    'category' => $param_array
                ];
            }else{
                $data = [
                    'code' => 400,
                    'status' => 'error',
                    'error' => $category
                ];
            }
        }else{
            $data = [
                'code' => 400,
                'status' => 'error',
                'error' => 'Debe pasar el nombre de la categoría'
            ];
        }
        // Devolver respuesta
        return response()->json($data, $data['code']);
    }
}
