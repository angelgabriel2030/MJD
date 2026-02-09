<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MascotaModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'animal',
        'edad',
        'descripcion',
        'raza',
        'juguete_nombre',
        'dulce_nombre',
        'observaciones'
    ];

    protected $casts = [
        'edad' => 'integer',
    ];

    public function imagenes()
    {
        return $this->morphMany(Imagen::class, 'imageable');
    }
}