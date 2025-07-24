<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\JwtAuth;

class JwtMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        // Obtener el token de la cabecera Authorization
        $token = $request->header('Authorization');

        // Instanciar Helper
        $jwtAuth = new JwtAuth();

        // Verificar el token
        if(!is_null($token)){
            $checkToken = $jwtAuth->checkToken($token);
        }else {
            return response()->json([
                'status' => 'error',
                'message' => 'La cabecera Authorization no existe',
            ], 401);
        }

        // Vincular el obejto identity a la request
        if($checkToken){
            // Asignamos la propiedad 'user' a la request con los datos del usuario
            $request->attributes->set('user', $jwtAuth->checkToken($token, true));
            // Continuar con la petición
            return $next($request);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Token no válido',
            ], 401);
        }   

    }
}
