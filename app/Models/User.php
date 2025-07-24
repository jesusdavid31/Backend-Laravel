<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{

    // Especificar la tabla
    protected $table = 'users';

    // Especificar los campos
    protected $fillable = [
        'name',
        'surname',
        'nick',
        'email',
        'bio',
        'avatar',
        'password',
        'created_at',
        'updated_at',
    ];

    // Especificar que campos no se pueden mostrar
    protected $hidden = [
        'password',
        'updated_at',
    ];
    
}
