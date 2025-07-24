<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Article;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ArticleController extends Controller
{
    public function save(Request $request)
    {
        // Sacar los datos del usuario identificado
        $identity = $request->attributes->get('user');

        // Crear el artículo a guardar directamente desde el request
        $articleToSave = $request->only(['title', 'content']);
        $articleToSave['user_id'] = $identity->sub;

        // Validar los datos
        $validator = Validator::make($articleToSave, [
            'title' => 'required|string|max:100',
            'content' => 'required|string|max:500',
        ]);

        // Crear respuesta en caso de errores
        if ($validator->fails()) {
            $data = [
                'status' => 'error',
                'message' => 'Los datos del artículo no son válidos',
                'errors' => $validator->errors(),
            ];
        }else {
            // Guardar el artículo
            $article = Article::create($articleToSave);

            // Preparar la respuesta
            $data = [
                'status' => 'success',
                'message' => 'El artículo se ha creado correctamente',
                'article' => $article,
            ];
        }

        // Devolver la respuesta
        return response()->json($data);
    }

    public function getArticles(Request $request, $page = 1)
    {
        // Verificar que $page sea un número entero positivo
        if (!filter_var($page, FILTER_VALIDATE_INT) || (int)$page < 1) {
            return response()->json([
                'status' => 'error',
                'message' => 'El parámetro page debe ser un número entero positivo',
            ], 400);
        }

        $itemsPerPage = 5; // Número de artículos por página

        $articles = Article::orderBy('created_at', 'desc')
            ->paginate($itemsPerPage, ['*'], 'page', $page);

        return response()->json([
            'status' => 'success',
            'currentPage' => $articles->currentPage(),
            'totalPages' => $articles->lastPage(),
            'itemsPerPage' => $itemsPerPage,
            'totalArticles' => $articles->total(),
            'articles' => $articles->items(),
        ])->setStatusCode(200);
    }

    public function getArticle (Request $request, $id)
    {
        // Buscar el artículo por ID
        $article = Article::with('user')->find($id);

        if (!$article) {
            return response()->json([
                'status' => 'error',
                'message' => 'Artículo no encontrado',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'article' => $article,
        ]);
    }

    public function getArticlesByUser(Request $request, $userId)
    {
        // Buscar artículos por ID de usuario
        $articles = Article::where('user_id', $userId)->orderBy('created_at', 'desc')->get();

        if ($articles->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se encontraron artículos para este usuario',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'articles' => $articles,
            'totalArticles' => $articles->count(),
        ]);
    }

    public function search ($searchTerm)
    {
        $articles = Article::where('title', 'LIKE', '%' . $searchTerm . '%')
            ->orWhere('content', 'LIKE', '%' . $searchTerm . '%')
            ->orderBy('created_at', 'desc')
            ->get();

        if ($articles->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se encontraron artículos que coincidan con la búsqueda',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'articles' => $articles,
            'totalArticles' => $articles->count(),
        ]);
    }

    public function delete(Request $request, $id)
    {
        // Buscar el artículo por ID
        $article = Article::find($id);

        if (!$article) {
            return response()->json([
                'status' => 'error',
                'message' => 'Artículo no encontrado',
            ], 404);
        }

        // Eliminar el artículo
        $article->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'El artículo se ha eliminado correctamente',
        ]);
    }

    public function update(Request $request, $id)
    {

        $identity = $request->attributes->get('user');

        // Buscar el artículo por ID
        $article = Article::where('id', $id)
            ->where('user_id', $identity->sub) // Asegurarse de que el usuario es el propietario del artículo
            ->first();

        if (!$article) {
            return response()->json([
                'status' => 'error',
                'message' => 'Artículo no encontrado',
            ], 404);
        }

        // Validar los datos del artículo
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:100',
            'content' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Los datos del artículo no son válidos',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Actualizar el artículo
        $article->update($request->only(['title', 'content']));

        return response()->json([
            'status' => 'success',
            'message' => 'El artículo se ha actualizado correctamente',
            'article' => $article,
        ]);
    }

    public function uploadImage(Request $request, $id)
    {
        // Sacar la imagen del request
        $image = $request->file('file0');

        // Obtenemos el artículo
        $article = Article::find($id);

        // Validar archivo
        $validator = Validator::make($request->all(), [
            'file0' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Máximo 2MB
        ]);

        // Comprobar la validación
        if ($image && empty($validator->fails())) {

            // Crear un nombre único para la imagen
            $imageName = time() . '_' . $image->getClientOriginalName();

            // Mover la imagen al directorio de usuarios
            Storage::disk('posters')->put($imageName, file_get_contents($image));

            // Si ya tiene una imagen, eliminarla
            if ($article && $article->image && Storage::disk('posters')->exists($article->image)) {
                Storage::disk('posters')->delete($article->image);
            }

            // Actualizar el campo imagen del artículo
            if ($article) {
                $article->image = $imageName;
                $article->save();
                $data = [
                    'status' => 'success',
                    'message' => 'Imagen subida correctamente',
                    'article' => $article
                ];
            } else {
                $data = [
                    'status' => 'error',
                    'message' => 'Artículo no encontrado'
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

    public function poster($file)
    {
        $disk = Storage::disk('posters');

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
