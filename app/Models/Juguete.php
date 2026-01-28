<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Juguete extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'tipo',
        'precio',
        'mascota_nombre',
        'dulce_nombre',
        'observaciones'
    ];

    protected $casts = [
        'precio' => 'decimal:2',
    ];

    public function imagenes()
    {
        return $this->morphMany(Imagen::class, 'imageable');
    }
}