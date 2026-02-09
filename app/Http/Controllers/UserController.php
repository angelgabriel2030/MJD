<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;


class UserController extends Controller
{
    public function login(Request $request){

        $request->validate(
            [
                'email' => 'required|email',
                'password' => 'required'
            ]
        );
        
        $correo = $request->email;
        $password = $request->password;

        $user = User::where('email','=', $correo)->first();
        
        if($user && Hash::check($password, $user->password)){
            //$user->tokens()->delete();
            $token = $user->createToken('token')->plainTextToken;
            return response()->json(['token' => $token, 'correo' => $user->email], 200);
        }

        return response()->json(['error' => 'Error de credenciales'], 401);
        
    }
}
