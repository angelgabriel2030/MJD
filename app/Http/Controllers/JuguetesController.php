<?php

namespace App\Http\Controllers;

use App\Models\Juguete;
use App\Models\Imagen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class JuguetesController extends Controller
{
    private string $dulcesServiceUrl = 'https://azariah-unbrittle-gwen.ngrok-free.dev/api/dulces';

    private string $mascotaServiceUrl = '';

    public function recibirDesdeMascota(Request $request)
    {
        try {
            $validated = $request->validate([
                'mascota' => 'required|array',
                'mascota.nombre' => 'required|string',
                'mascota.id' => 'nullable|integer',
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

            $dulceData = $this->generarDulceParaMascota($validated['mascota']);

            $payload = [
                'mascota' => [
                    'id' => $validated['mascota']['id'] ?? null,
                    'nombre' => $validated['mascota']['nombre'],
                ],
                'juguete' => [
                    'id' => $juguete->id,
                    'nombre' => $juguete->nombre,
                    'tipo' => $juguete->tipo,
                    'precio' => $juguete->precio,
                ],
                'dulce' => $dulceData
            ];

            Log::info('Enviando a Dulces', ['payload' => $payload]);

            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post($this->dulcesServiceUrl, $payload);

            if ($response->successful()) {
                $responseData = $response->json();
                
                $juguete->update([
                    'dulce_nombre' => $responseData['dulce']['nombre'] ?? null,
                    'observaciones' => 'Completado - Dulce asignado'
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Juguete procesado y enviado a Dulces exitosamente',
                    'juguete' => $juguete->load('imagenes'),
                    'dulces_response' => $responseData
                ], 200);
            }

            Log::error('Error al comunicar con Dulces', [
                'status' => $response->status(),
                'body' => $response->body(),
                'payload_sent' => $payload
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Juguete guardado pero error al comunicar con Dulces',
                'juguete' => $juguete->load('imagenes'),
                'error' => $response->body(),
                'status_code' => $response->status()
            ], 500);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Error de validación en Juguetes', [
                'errors' => $e->errors(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error en recibirDesdeMascota', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error al procesar juguete',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function generarDulceParaMascota(array $mascota): array
    {
        $nombreMascota = strtolower($mascota['nombre'] ?? 'desconocido');
                $dulces = [
            'perro' => ['nombre' => 'Hueso de caramelo', 'sabor' => 'pollo'],
            'gato' => ['nombre' => 'Gomita de atún', 'sabor' => 'pescado'],
            'conejo' => ['nombre' => 'Zanahoria dulce', 'sabor' => 'zanahoria'],
            'hamster' => ['nombre' => 'Bolita de miel', 'sabor' => 'miel'],
            'default' => ['nombre' => 'Galleta especial', 'sabor' => 'vainilla']
        ];

        foreach ($dulces as $tipo => $dulce) {
            if ($tipo !== 'default' && str_contains($nombreMascota, $tipo)) {
                return $dulce;
            }
        }

        return $dulces['default'];
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

            if (!empty($this->mascotaServiceUrl)) {
                $response = Http::timeout(30)->post($this->mascotaServiceUrl, [
                    'mascota' => $validated['mascota'],
                    'juguete' => $validated['juguete'],
                    'dulce' => $validated['dulce']
                ]);

                if ($response->successful()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Datos enviados de vuelta a Mascota exitosamente',
                        'data' => $validated,
                        'mascota_response' => $response->json()
                    ], 200);
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Datos recibidos pero error al comunicar con Mascota',
                    'data' => $validated,
                    'error' => $response->body()
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Datos procesados exitosamente',
                'data' => $validated
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error en recibirDesdeDulces', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
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
            'success' => true,
            'juguetes' => $juguetes,
            'total' => $juguetes->count()
        ], 200);
    }

    public function show($id)
    {
        $juguete = Juguete::with('imagenes')->findOrFail($id);

        return response()->json([
            'success' => true,
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
            'success' => true,
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