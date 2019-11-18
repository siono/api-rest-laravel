<?php

namespace App\Http\Controllers;

use App\Helpers\JwtAuth;
use App\User;
use Illuminate\Http\Request;
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
}
