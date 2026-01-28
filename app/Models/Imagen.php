<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Imagen extends Model
{
    use HasFactory;

    protected $table = 'imagenes';

    protected $fillable = [
        'url',
        'ip_origen',
        'imageable_id',
        'imageable_type',
        'origen',
        'datos_peticion'
    ];

    protected $casts = [
        'datos_peticion' => 'array',
    ];

    public function imageable()
    {
        return $this->morphTo();
    }
}