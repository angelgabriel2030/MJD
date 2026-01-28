<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mascota;
use Illuminate\Support\Facades\Hash;

class MascotaController extends Controller
{
    public function login(Request $request){

        $request->validate(
            [
                'nombre' => 'required',
            ]
        );
        
        $nombre = $request-> nombre;

        $mascota = mascota::where('nombre','=', $nombre)->first();
        
        if($mascota){
          
            return response()->json([ 'nombre' => $mascota->nombre,'animal' => $mascota->animal,'edad' => $mascota->edad,'descripcion' => $mascota->descripcion,'raza' => $mascota->raza], 200);
        }

        return response()->json(['error' => 'Error de credenciales'], 401);
        
    }
}
