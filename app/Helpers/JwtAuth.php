<?php

namespace App\Helpers;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;
use App\User;


class JwtAuth{

    //buscar si existe el usuario con sus credenciales
    //comprobar si son correctas(objeto)
    //generar el toke con los datos del usuario identificado
    //devolver los datos decodificados o el token, en función de un parametro

    public $key;

    public function __construct()
    {
        $this->key = 'esto_es_una_clave_super_segura';
    }

    public function signup($email,$password,$getToken=null){

        $user = User::where([
           'email' => $email,
           'password' => $password
        ])->first();

        $signup = false;

        if (is_object($user)){
            $signup = true;
        }

        //generamos el token
        if ($signup){
            $token = array(
                'sub'       => $user->id,
                'email'     => $user->email,
                'name'      => $user->name,
                'surname'   => $user->surname,
                'iat'       => time(),
                'exp'       => time() + (7 * 24 * 60 * 60)
            );

            $jwt = JWT::encode($token, $this->key, 'HS256');

            $decoded = JWT::decode($jwt, $this->key, ['HS256']);

            //Devolvemos los datos decodificados o el token, en función de un parametro.
            if (is_null($getToken)){
                $data = $jwt;
            }else{
                $data = $decoded;
            }

        }else{
            $data = array(
                'status'    => 'error',
                'message'   => 'Login incorrecto',
                'email' => $email,
                'password' => $password
            );
        }

        return $data;
    }


    public function checkToken($jwt, $getIdentity = false){
        $auth = false;

        try {
            $decoded = JWT::decode($jwt, $this->key, ['HS256']);
        }catch (\UnexpectedValueException $e){
            $auth = false;
        }catch (\DomainException $e){
            $auth = false;
        }

        if (!empty($decoded) && is_object($decoded) && isset($decoded->sub)){
            $auth = true;
        }

        if ($getIdentity){
            return $decoded;
        }

        return $auth;

    }
}
