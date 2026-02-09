<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{

    public function create(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users',
                'password' => 'required|string|min:6'
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);

            Log::info('Usuario creado en Juguetes', ['user_id' => $user->id]);

            $dulcesUrl = env('LAPTOP3_URL');

            try {
                $response = Http::timeout(10)->post("{$dulcesUrl}/api/create", [
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => $request->password
                ]);

                if ($response->successful()) {
                    Log::info('Usuario también creado en Dulces');
                } else {
                    Log::warning('No se pudo crear usuario en Dulces', [
                        'status' => $response->status(),
                        'body' => $response->body()
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Error al crear usuario en Dulces', ['error' => $e->getMessage()]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Usuario creado exitosamente',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email
                ]
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error al crear usuario en Juguetes', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error al crear usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required'
            ]);


            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email o contraseña incorrectos'
                ], 401);
            }

            $token = $user->createToken('auth-token')->plainTextToken;

            Log::info('Usuario logueado en Juguetes', ['user_id' => $user->id]);


            $dulcesUrl = env('LAPTOP3_URL');
            $dulcesToken = null;

            try {
                $response = Http::timeout(10)->post("{$dulcesUrl}/api/login", [
                    'email' => $request->email,
                    'password' => $request->password
                ]);

                if ($response->successful()) {
                    $dulcesToken = $response->json()['access_token'] ?? null;
                    Log::info('También logueado en Dulces');
                } else {
                    Log::warning('No se pudo hacer login en Dulces');
                }
            } catch (\Exception $e) {
                Log::error('Error al hacer login en Dulces', ['error' => $e->getMessage()]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Login exitoso',
                'access_token' => $token,
                'dulces_token' => $dulcesToken,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email
                ]
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error en login de Juguetes', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error en login',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout exitoso'
        ], 200);
    }
}
