<?php

namespace App\Helpers;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtAuth
{
    
    // Clave secreta para cifrar el token
    private $key;

    // Inicializar clave secreta en el constructor
    public function __construct(){
        $this->key = "clave-privada-backend-laravel-7785445648";
    }

    // Generar un token JWT para el usuario autenticado
    public function auth($user, $getUser = null){
        $signup = false;

        if (is_object($user)) {
            $signup = true;
        }

        if ($signup) {

            // Datos del payload
            $payload = [
                'sub' => $user->id, // ID del usuario
                'email' => $user->email, // Email del usuario
                'name' => $user->name, // Nombre del usuario
                'surname' => $user->surname, // Apellido del usuario
                'nick' => $user->nick, // Nick del usuario
                'bio' => $user->bio, // Biografía del usuario
                'avatar' => $user->avatar, // Imagen del usuario
                'created_at' => $user->created_at, // Fecha de creación del usuario
                'iat' => time(), // Fecha de creación del token
                'exp' => time() + (7 * 24 * 60 * 60) // Fecha de expiración (1 semana)
            ];

            // Codificar el payload
            $jwt = JWT::encode($payload, $this->key, 'HS256');

            if(is_null($getUser)){
                $data = $jwt;
            }else {

                // Decodificar el token para sacar los datos del usuario (getUser)
                $user_decoded = JWT::decode($jwt, new Key($this->key, 'HS256'));

                // Devolver la respuesta
                $data = $user_decoded;

            }
            
        }else {
            $data = [
                'status' => 'error',
                'message' => 'Login fallido',
            ];
        }

        return $data;
    }

    // Verificar la validez del token
    public function checkToken($jwt, $getIdentity = false){
        $auth = false;
        $decoded = false;

        try {
            // Decodificar el token
            $decoded = JWT::decode($jwt, new Key($this->key, 'HS256'));
        } catch (\UnexpectedValueException $e) {
            $auth = false;
        } catch (\DomainException $e) {
            $auth = false;
        }

        if ($decoded && !empty($decoded) && is_object($decoded) && isset($decoded->sub)) {
            $auth = true;
        } else {
            $auth = false;
        }

        // Si se solicita la identidad del usuario
        if ($getIdentity) {
            return $decoded;
        }

        return $auth;
    }
    

}