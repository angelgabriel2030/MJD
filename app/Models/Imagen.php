<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Imagen extends Model
{
    protected $table = 'imagenes';

    protected $fillable = ['url', 'descripcion', 'imageable_id', 'imageable_type'];

    /**
     * Relación polimórfica inversa
     */
    public function imageable()
    {
        return $this->morphTo();
    }
}
