<?php

namespace App\Http\Controllers;

use App\Models\Mascota;
use App\Models\Imagen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MascotaController extends Controller
{
    private string $jugueteServiceUrl = 'https://donte-trappiest-fruitfully.ngrok-free.dev/api/juguetes/desde-mascota';

    public function login(Request $request)
    {
        try {
            $validated = $request->validate([
                'nombre' => 'required|string',
            ]);

            $mascota = Mascota::create([
              'nombre'=>$request->nombre,
              'animal'=>$request->animal,
              'edad'=>$request->edad,
              'descripcion'=>$request->descripcion,
              'raza'=>$request->raza
            ]);
            
            $mascota = Mascota::where('nombre', $validated['nombre'])->first();
            
            if ($mascota) {
                Log::info('Login exitoso de mascota', ['mascota_id' => $mascota->id]);
                
                return response()->json([
                    'nombre' => $mascota->nombre,
                    'animal' => $mascota->animal,
                    'edad' => $mascota->edad,
                    'descripcion' => $mascota->descripcion,
                    'raza' => $mascota->raza
                ], 200);
            }

            Log::warning('Intento de login fallido', ['nombre' => $validated['nombre']]);
            
            return response()->json([
                'error' => 'Error de credenciales'
            ], 401);

        } catch (\Exception $e) {
            Log::error('Error en login de mascota', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Error al procesar login',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function index()
    {
        $mascotas = Mascota::with('imagenes')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json([
            'mascotas' => $mascotas,
            'total' => $mascotas->count()
        ], 200);
    }

    public function show($id)
    {
        $mascota = Mascota::with('imagenes')->findOrFail($id);
        
        return response()->json([
            'mascota' => $mascota
        ], 200);
    }

     public function enviarAJuguete(Request $request)

    {

        try {

            $validated = $request->validate([

                'mascota' => 'required|array',
                'mascota.nombre' => 'required|string',
                'mascota.animal' => 'required|string',
                'juguete' => 'required|array',
               'juguete.nombre' => 'required|string',
                'juguete.tipo' => 'required|string',
                'juguete.precio' => 'required|numeric',
            ]);

            $mascota = Mascota::where('nombre', $validated['mascota']['nombre'])->first();

            if (!$mascota) {
                return response()->json([
                    'error' => 'Mascota no encontrada'
                ], 404);
            }

            Log::info('Enviando datos a Juguete', [
                'mascota_id' => $mascota->id,
                'url_destino' => $this->jugueteServiceUrl
            ]);

            $response = Http::timeout(30)->post($this->jugueteServiceUrl, [
                'mascota' => [
                    'id' => $mascota->id,
                    'nombre' => $mascota->nombre,
                    'animal' => $mascota->animal,
                    'edad' => $mascota->edad,
                    'raza' => $mascota->raza,
               ],
                 'juguete' => $validated['juguete']
            ]);

            Log::info('Respuesta de Juguete', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if ($response->successful()) {
                return response()->json([
                    'message' => 'Datos enviados a Juguete exitosamente',
                    'mascota' => $mascota,
                    'juguete_response' => $response->json()
                ], 200);
            }

            return response()->json([
                'message' => 'Error al comunicar con Juguete',
                'mascota' => $mascota,
                'error_detail' => $response->body(),
                'status_code' => $response->status()
            ], 500);

        } catch (\Exception $e) {
            Log::error('Error en enviarAJuguete', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                 'error' => 'Error al procesar solicitud',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function recibirDesdeDulces(Request $request)
    {
        try {
            $validated = $request->validate([
                'mascota' => 'required|array',
                'juguete' => 'required|array',
                'dulce' => 'required|array',
            ]);

            Log::info('Respuesta final recibida desde ciclo completo', $validated);

            if (isset($validated['mascota']['id'])) {
                $mascota = Mascota::find($validated['mascota']['id']);
                
                if ($mascota) {
                    $this->guardarImagen($request, $mascota, 'ciclo_completo');
                }
            }

            return response()->json([
                'message' => 'Ciclo completado exitosamente',
                'data' => $validated
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error en recibirDesdeDulces', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'error' => 'Error al procesar respuesta final',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function guardarImagen(Request $request, Mascota $mascota, string $origen)
    {
        $url = $request->url();
        $ipOrigen = $request->ip();
        
        if ($request->header('X-Forwarded-For')) {
            $forwardedIps = explode(',', $request->header('X-Forwarded-For'));
            $ipOrigen = trim($forwardedIps[0]);
        }

        $imagen = new Imagen([
            'url' => $url,
            'ip_origen' => $ipOrigen,
            'origen' => $origen,
            'datos_peticion' => [
                'method' => $request->method(),
                'headers' => [
                    'user-agent' => $request->userAgent(),
                    'referer' => $request->header('Referer'),
                    'origin' => $request->header('Origin'),
                ],
                'payload' => $request->all(),
                'timestamp' => now()->toDateTimeString(),
            ]
        ]);

        $mascota->imagenes()->save($imagen);

        Log::info('Imagen guardada para mascota', [
            'imagen_id' => $imagen->id,
            'origen' => $origen,
            'url' => $url,
            'ip' => $ipOrigen
        ]);

        return $imagen;
    }
}