<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Juguete extends Model
{
  use HasFactory;
  protected $table = 'juguetes';

  protected $fillable = [
    'nombre',
    'color',
    'marca',
    'descripcion'
  ];

}
