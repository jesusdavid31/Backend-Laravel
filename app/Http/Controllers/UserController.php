<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Helpers\JwtAuth;

class UserController extends Controller
{
    

    public function register(Request $request)
    {

        // Recoger los datos del request
        $name = $request->input('name', null);
        $surname = $request->input('surname', null);
        $nick = $request->input('nick', null);
        $email = $request->input('email', null);
        $bio = $request->input('bio', null);
        $password = $request->input('password', null);

        // Crear array con los datos
        $user = [
            'name' => $name,
            'surname' => $surname,
            'email' => $email,
            'nick' => $nick,
            'bio' => $bio,
        ];

        // Eliminar espacios en blanco
        $user_trim = array_map('trim', $user);
        $user_trim['password'] = $password;

        // Validar los datos
        $validator = Validator::make($user_trim, [
            'name' => 'required|string|max:50',
            'surname' => 'required|string|max:100',
            'nick' => 'required|string|max:50|unique:users',
            'email' => 'required|email|max:100|unique:users',
            'bio' => 'nullable|string|max:255',
            'password' => 'required|string|min:8',
        ]);

        // Crear respuesta en caso de errores
        $response = [
            'status' => 'error',
            'message' => 'El usuario no se ha creado',
            'errors' => $validator->errors(),
        ];

        // Verificar si la validación falla
        if ($validator->fails()) {
            return response()->json($response, 422);
        }else {

            // Cifrar la contraseña
            $pwd = password_hash($user_trim['password'], PASSWORD_BCRYPT, ['cost' => 5]);

            // Crear un objeto User
            $user = new User($user_trim);
            $user->password = $pwd;

            // Guardar el usuario en la base de datos
            $user->save();

            // Devolver una respuesta
            return response()->json([
                'status' => 'success',
                'message' => 'Usuario creado correctamente',
                'user' => $user
            ], 201);

        }
    }

    public function login(Request $request)
    {

        // Instanciar JwtAuth
        $jwtAuth = new JwtAuth();

        // Recoger los datos del request
        $email = $request->input('email', null);
        $password = $request->input('password', null);
        $getUser = $request->input('getUser', null);

        // Metemos los datos en un array
        $credentials = [
            'email' => $email,
            'password' => $password,
        ];

        // Validar los datos
        $validator = Validator::make($credentials, [
            'email' => 'required|email|max:100',
            'password' => 'required|string',
        ]);

        // Crear respuesta en caso de errores
        $errorResponse = [
            'status' => 'error',
            'message' => 'El usuario no se ha podido validar correctamente',
            'errors' => $validator->errors(),
        ];

        // Verificar si la validación falla
        if ($validator->fails()) {
            // Si falla la validación, devolver un error
            return response()->json($errorResponse, 422);
        } else {
            // Buscar el usuario por email
            $user = User::where('email', $email)->first();

            // Verificar si el usuario existe y la contraseña es correcta
            if ($user && password_verify($password, $user->password)) {

                if(is_null($getUser)) {
                    return $jwtAuth->auth($user);
                } else {
                    return $jwtAuth->auth($user, true);
                }
                
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Credenciales incorrectas'
                ], 401);
            }
        }
    }

    public function update(Request $request){

        // Obtener los datos del usuario autenticado
        $identity = $request->attributes->get('user');

        // Recoger los datos del request
        $name = $request->input('name', $identity->name);
        $surname = $request->input('surname', $identity->surname);
        $nick = $request->input('nick', $identity->nick);
        $email = $request->input('email', $identity->email);
        $bio = $request->input('bio', $identity->bio);

        // Array para validar los datos
        $userToUpdate = [
            'name' => $name,
            'surname' => $surname,
            'nick' => $nick,
            'email' => $email,
            'bio' => $bio,
        ];

        // Recoger contraseña en caso de que se quiera actualizar
        $password = $request->input('password', null);

        // Cifrar la contraseña si se ha proporcionado
        if (!is_null($password)) {
            $pwd = password_hash($password, PASSWORD_BCRYPT, ['cost' => 5]);
            $userToUpdate['password'] = $pwd;
        }

        // Validar los datos
        $validator = Validator::make($userToUpdate, [
            'name' => 'required|string|max:50',
            'surname' => 'required|string|max:100',
            // Validamos que el email sea único en la tabla users excepto para el usuario autenticado
            'email' => 'required|email|max:100|unique:users,email,'.$identity->sub,
            // Validamos que el nick sea único en la tabla users excepto para el usuario autenticado
            'nick' => 'required|string|max:50|unique:users,nick,'.$identity->sub,
            'bio' => 'nullable|string|max:255',
            'password' => 'nullable|string',
        ]);

        // Comprobar la validación
        if ($validator->fails()) {
            $data = [
                'status' => 'error',
                'message' => 'Error al actualizar el usuario',  
                'errors' => $validator->errors(),
            ];
        }else{
            // Actualizar el usuario
            $userUpdated = User::where('id', $identity->sub)->update($userToUpdate);
            $data = [
                'status' => 'success',
                'message' => 'Usuario actualizado correctamente',
                'user' => $userToUpdate
            ];
        }

        // Devolver una respuesta
        return response()->json($data);

    }

    public function profile(Request $request, $id)
    {
        // Buscar el usuario por ID
        $user = User::where('id', $id)->first();

        // Verificar si el usuario existe
        if (!$user) {
            $data = [
                'status' => 'error',
                'message' => 'Usuario no encontrado'
            ];
        }else{
            $user->makeHidden(['password', 'updated_at']);
            $data = [
                'status' => 'success',
                'user' => $user
            ];
        }

        return response()->json($data);
    }

    public function upload(Request $request)
    {

        // Sacar la imagen del request
        $image = $request->file('file0');

        // Obtener los datos del usuario autenticado
        $identity = $request->attributes->get('user');

        // Validar archivo
        $validator = Validator::make($request->all(), [
            'file0' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Comprobar la validación
        if ($image && empty($validator->fails())) {

            // Crear un nombre único para la imagen
            $imageName = time() . '_' . $image->getClientOriginalName();

            // Mover la imagen al directorio de usuarios
            Storage::disk('avatars')->put($imageName, file_get_contents($image));

            // Buscar el usuario por ID
            $user = User::find($identity->sub);

            // Si ya tiene una imagen, eliminarla
            if ($user && $user->avatar && Storage::disk('avatars')->exists($user->avatar)) {
                Storage::disk('avatars')->delete($user->avatar);
            }

            // Actualizar el campo avatar del usuario
            if ($user) {
                $user->avatar = $imageName;
                $user->save();
                $data = [
                    'status' => 'success',
                    'message' => 'Imagen subida correctamente',
                    'user' => $user
                ];
            } else {
                $data = [
                    'status' => 'error',
                    'message' => 'Usuario no encontrado'
                ];
            }
            
        }else{
            return response()->json([
                'status' => 'error',
                'message' => 'Error al subir la imagen',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Devolver una respuesta
        return response()->json($data);

    }
    
    public function getAvatar($file)
    {
        $disk = Storage::disk('avatars');

        if ($disk->exists($file)) {
            $filePath = $disk->path($file);
            return response()->file($filePath);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Imagen no encontrada'
            ], 404);
        }
    }

}
