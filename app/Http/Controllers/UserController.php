<?php

namespace App\Http\Controllers;

use App\Helpers\JwtAuth;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function register(Request $request){

        // Recoger los datos del usuario por post
        $json = $request->input('json',null);

        $param = \json_decode($json,true);

        $param = array_map('trim', $param);//limpiamos los espacios en blanco.

        // Validar los datos
        $validate = Validator::make($param,[
            'name' => 'required|alpha',
            'surname' => 'required|alpha',
            'email' => 'required|email|unique:users',
            'password' => 'required',
        ]);

        if ($validate->fails()){
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'El usuario no se ha creado correctamente',
                'errors' => $validate->errors()
            );
            return response()->json($data,400);
        }

        //ciframos la contraseña
//        $pwd = password_hash($param['password'], PASSWORD_BCRYPT, ['cost'=>4]);
        $pwd = hash('sha256',$param['password']);

        $user = new User();

        $user->name = $param['name'];
        $user->surname = $param['surname'];
        $user->email = $param['email'];
        $user->password = $pwd;
        $user->role = 'USER_ROLE';

        $user->save();

        $data = array(
            'status' => 'success',
            'code' => 200,
            'message' => 'El usuario se ha creado correctamente',
            'user' => $user
        );
        return response()->json($data,200);

    }

    public function login(Request $request){

        $jwtAuth = new JwtAuth();

        //Recibir los datos por POST
        $json = $request->input('json', null);
        $params = json_decode($json,true);

        // Validar los datos
        $validate = Validator::make($params,[
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validate->fails()){
            $signup = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'El usuario no se ha podido loguear',
                'errors' => $validate->errors()
            );

        }else{
            //cifrar password
            $pwd = hash('sha256',$params['password']);

            $signup = $jwtAuth->signup($params['email'],$pwd);
            if (!empty($params['getToken'])){
                $signup = $jwtAuth->signup($params['email'],$pwd,true);

            }

        }

        return response()->json($signup,200);
    }

    public function update(Request $request)
    {
        //comprobar si el usuario está identificado.
        $token = $request->header('Authorization');
        $jwtAuth = new JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);

        $json = $request->input('json', null);
        $param_array = json_decode($json, true);

        if ($checkToken && !empty($param_array)) {
            $user = $jwtAuth->checkToken($token, true);

            $validate = Validator::make(
                $param_array,
                [
                    'name' => 'required|alpha',
                    'surname' => 'required|alpha',
                    'email' => 'required|email|unique:users'.$user->sub,
                ]
            );

            //quitamos los campos que no quiero actualizar.
            unset(
                $param_array['id'],
                $param_array['role'],
                $param_array['password'],
                $param_array['created_at'],
                $param_array['remember_token']
            );

            //Actualizamos usuario
            $user_update = User::where('id', $user->sub)->update($param_array);

            $data = [
                'code' => 200,
                'status' => 'ok',
                'message' => 'El usuario esta identificado',
                'user' => $user_update,
            ];
        } else {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'El usuario no esta identificado',
            ];
        }

        return response()->json($data, $data['code']);
    }

    public function upload(Request $request)
    {
        //recoger datos de la peticion
        $image = $request->file('file0');

        //validar la imagen
        $validate = Validator::make(
            $request->all(),
            [
                'file0' => 'required|image|mimes:jpg,jpeg,png,gif',
            ]
        );

        //guardar imagen
        if ($validate) {
            $image_name = time().$image->getClientOriginalName();
            Storage::disk('users')->put($image_name, File::get($image));

            $data = [
                'code' => 200,
                'status' => 'success',
                'image' => $image_name,
            ];
        } else {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'Error al subir imagen',
            ];
        }

        return response($data, $data['code'])->header('Content-Type', 'text/plain');
    }

    public function getImage($filename)
    {
        $isset = Storage::disk('users')->exists($filename);
        if ($isset) {
            $file = Storage::disk('users')->get($filename);

            return new Response($file, 200);
        } else {
            $data = [
                'code' => 400,
                'status' => 'error',
                'message' => 'No existe el avatar con ese nombre',
            ];

            return response($data, $data['code'])->header('Content-Type', 'text/plain');
        }
    }

    public function detail($id)
    {
        $user = User::find($id);

        if (is_object($user)) {
            $data = array(
                'code' => 200,
                'status' => 'success',
                'user' => $user,
            );
        } else {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'user' => 'Usuario no existe',
            );
        }
        return \response()->json($data,$data['code']);
    }
}
