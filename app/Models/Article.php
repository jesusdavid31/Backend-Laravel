<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{

    // Especificar la tabla
    protected $table = 'articles';

    // Especificar los campos
    protected $fillable = [
        'title',
        'content',
        'user_id',
        'image',
        'created_at',
        'updated_at',
    ];

    // Relación con el modelo User
    public function user()
    {
        return $this->belongsTo("App\Models\User", 'user_id');
    }
    
}
