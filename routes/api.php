<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
use App\Http\Controllers\ArticleController;

// Rutas públicas de usuarios
Route::post('/user/register', [UserController::class, 'register']);
Route::post('/user/login', [UserController::class, 'login']);
Route::get('/user/profile/{id}', [UserController::class, 'profile']);
Route::get('/user/avatar/{file}', [UserController::class, 'getAvatar']);

// Rutas públicas de artículos
Route::get('/articles/items/{page}', [ArticleController::class, 'getArticles']);
Route::get('/articles/item/{id}', [ArticleController::class, 'getArticle']);
Route::get('/articles/user/{userId}', [ArticleController::class, 'getArticlesByUser']);
Route::get('/articles/search/{searchTerm}', [ArticleController::class, 'search']);
Route::get('/articles/poster/{file}', [ArticleController::class, 'poster']);

// Con middleware: requiere token
Route::middleware(['jwt.auth'])->group(function () {
    // Rutas para usuarios
    Route::put('/user/update', [UserController::class, 'update']);
    Route::post('/user/upload', [UserController::class, 'upload']);

    // Rutas para artículos
    Route::post('/articles/save', [ArticleController::class, 'save']);
    Route::delete('/articles/delete/{id}', [ArticleController::class, 'delete']);
    Route::put('/articles/update/{id}', [ArticleController::class, 'update']);
    Route::post('/articles/upload/{id}', [ArticleController::class, 'uploadImage']);
});
