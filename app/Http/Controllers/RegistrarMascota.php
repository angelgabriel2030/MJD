<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mascota;
use Illuminate\Support\Facades\Http;

class RegistrarMascota extends Controller
{
   public function registrar(Request $request)
   {
     $mascota = Mascota::create([
       'nombre'=>$request->nombre,
       'animal'=>$request->animal,
       'edad'=>$request->edad,
       'descripcion'=>$request->descripcion,
       'raza'=>$request->raza
     ]);

     $response = Http::withoutVerifying()->post(env("NGROK"),[
        'mascota_id'=>$mascota->id,
        'nombre_dulce'=>$request->nombre_dulce,
        'color_dulce'=>$request->color_dulce,
        'marca_dulce'=>$request->marca_dulce,
        'nombre_juguete'=>$request->nombre_juguete,
        'color_juguete'=>$request->color_jugute,
        'edad_juguete'=>$request->edad_juguete
    ]);
     

      return response()->json([ 'Creado Corectamente',], 200);

   }
}
