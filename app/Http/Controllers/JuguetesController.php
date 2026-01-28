<?php

namespace App\Http\Controllers;

use App\Models\Juguete;
use App\Models\Imagen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class JuguetesController extends Controller
{
    private string $dulcesServiceUrl = 'https://azariah-unbrittle-gwen.ngrok-free.dev';

    public function recibirDesdeMascota(Request $request)
    {
        try {
            $validated = $request->validate([
                'mascota' => 'required|array',
                'mascota.nombre' => 'required|string',
                'juguete' => 'required|array',
                'juguete.nombre' => 'required|string',
                'juguete.tipo' => 'required|string',
                'juguete.precio' => 'required|numeric',
            ]);

            $juguete = Juguete::create([
                'nombre' => $validated['juguete']['nombre'],
                'tipo' => $validated['juguete']['tipo'],
                'precio' => $validated['juguete']['precio'],
                'mascota_nombre' => $validated['mascota']['nombre'],
                'observaciones' => 'Recibido desde Mascota'
            ]);

            $this->guardarImagen($request, $juguete, 'mascota');

            Log::info('Juguete guardado desde Mascota', ['juguete_id' => $juguete->id]);

            $response = Http::timeout(30)->post($this->dulcesServiceUrl, [
                'mascota' => $validated['mascota'],
                'juguete' => [
                    'id' => $juguete->id,
                    'nombre' => $juguete->nombre,
                    'tipo' => $juguete->tipo,
                    'precio' => $juguete->precio,
                ],
                'dulce' => $request->input('dulce', [])
            ]);

            if ($response->successful()) {
                $juguete->update(['dulce_nombre' => $response->json()['dulce']['nombre'] ?? null]);
                
                return response()->json([
                    'message' => 'Juguete procesado y enviado a Dulces exitosamente',
                    'juguete' => $juguete->load('imagenes'),
                    'dulces_response' => $response->json()
                ], 200);
            }

            return response()->json([
                'message' => 'Juguete guardado pero error al comunicar con Dulces',
                'juguete' => $juguete->load('imagenes'),
                'error' => $response->body()
            ], 500);

        } catch (\Exception $e) {
            Log::error('Error en recibirDesdeMascota', ['error' => $e->getMessage()]);
            
            return response()->json([
                'error' => 'Error al procesar juguete',
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

            Log::info('Respuesta recibida desde Dulces', $validated);

            if (isset($validated['juguete']['id'])) {
                $juguete = Juguete::find($validated['juguete']['id']);
                if ($juguete) {
                    $juguete->update([
                        'dulce_nombre' => $validated['dulce']['nombre'] ?? null,
                        'observaciones' => 'Completado ciclo completo'
                    ]);

                    $this->guardarImagen($request, $juguete, 'dulce');
                }
            }

            return response()->json([
                'message' => 'Datos recibidos correctamente desde Dulces',
                'data' => $validated
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error en recibirDesdeDulces', ['error' => $e->getMessage()]);
            
            return response()->json([
                'error' => 'Error al procesar respuesta de Dulces',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    
    public function index()
    {
        $juguetes = Juguete::with('imagenes')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json([
            'juguetes' => $juguetes,
            'total' => $juguetes->count()
        ], 200);
    }

    public function show($id)
    {
        $juguete = Juguete::with('imagenes')->findOrFail($id);
        
        return response()->json([
            'juguete' => $juguete
        ], 200);
    }

    
    public function listarImagenes()
    {
        $imagenes = Imagen::where('imageable_type', Juguete::class)
            ->with('imageable')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json([
            'imagenes' => $imagenes,
            'total' => $imagenes->count()
        ], 200);
    }

    private function guardarImagen(Request $request, Juguete $juguete, string $origen)
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

        $juguete->imagenes()->save($imagen);

        Log::info('Imagen guardada', [
            'imagen_id' => $imagen->id,
            'origen' => $origen,
            'url' => $url,
            'ip' => $ipOrigen
        ]);

        return $imagen;
    }
}