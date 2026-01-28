<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dulce extends Model
{
    protected $table = 'dulces';
    protected $fillable = ['mascota_id', 'nombre', 'sabor', 'apto_mascotas'];

    protected $casts = [
        'apto_mascotas' => 'boolean'
    ];

    public function imagenes()
    {
        return $this->morphMany(Imagen::class, 'imageable');
    }
}
