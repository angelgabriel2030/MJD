<?php

namespace App\Http\Controllers;

use App\Models\Mascota;
use App\Models\Imagen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MascotaController extends Controller
{
    private string $jugueteServiceUrl;

    public function __construct()
    {
        $this->jugueteServiceUrl = env('LAPTOP2_URL') . '/api/juguetes/desde-mascota';
    }


    public function registrar(Request $request)
    {
        try {
            $validated = $request->validate([
                'mascota' => 'required|array',
                'mascota.nombre' => 'required|string',
                'mascota.animal' => 'required|string',
                'mascota.edad' => 'required|integer',
                'mascota.raza' => 'required|string',
                'juguete' => 'required|array',
                'juguete.nombre' => 'required|string',
                'juguete.tipo' => 'required|string',
                'juguete.precio' => 'required|numeric',
                'dulce' => 'required|array',
                'dulce.nombre' => 'required|string',
                'dulce.sabor' => 'required|string',
            ]);


            $mascota = Mascota::create([
                'nombre' => $validated['mascota']['nombre'],
                'animal' => $validated['mascota']['animal'],
                'edad' => $validated['mascota']['edad'],
                'descripcion' => $validated['mascota']['descripcion'] ?? '',
                'raza' => $validated['mascota']['raza'],
            ]);

            Log::info('Mascota creada', ['mascota_id' => $mascota->id]);


            $juguetesToken = env('JUGUETES_TOKEN');


            $response = Http::withToken($juguetesToken)
                ->timeout(30)
                ->post($this->jugueteServiceUrl, [
                    'mascota' => [
                        'id' => $mascota->id,
                        'nombre' => $mascota->nombre,
                        'animal' => $mascota->animal,
                        'edad' => $mascota->edad,
                        'raza' => $mascota->raza,
                    ],
                    'juguete' => $validated['juguete'],
                    'dulce' => $validated['dulce'],
                ]);

            Log::info('Respuesta de Juguetes', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Mascota registrada y enviada exitosamente',
                    'mascota' => $mascota,
                    'flujo_completo' => $response->json()
                ], 201);
            }

            return response()->json([
                'success' => false,
                'message' => 'Mascota creada pero error al comunicar con Juguetes',
                'mascota' => $mascota,
                'error' => $response->body()
            ], 500);

        } catch (\Exception $e) {
            Log::error('Error al registrar mascota', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar solicitud',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function enviarAJuguete(Request $request)
    {
        try {
            $validated = $request->validate([
                'mascota' => 'required|array',
                'mascota.nombre' => 'required|string',
                'juguete' => 'required|array',
                'juguete.nombre' => 'required|string',
                'juguete.tipo' => 'required|string',
                'juguete.precio' => 'required|numeric',
                'dulce' => 'required|array',
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

            $juguetesToken = env('JUGUETES_TOKEN');

            $response = Http::withToken($juguetesToken)
                ->timeout(30)
                ->post($this->jugueteServiceUrl, [
                    'mascota' => [
                        'id' => $mascota->id,
                        'nombre' => $mascota->nombre,
                        'animal' => $mascota->animal,
                        'edad' => $mascota->edad,
                        'raza' => $mascota->raza,
                    ],
                    'juguete' => $validated['juguete'],
                    'dulce' => $validated['dulce'],
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
                'error' => $response->body()
            ], 500);

        } catch (\Exception $e) {
            Log::error('Error en enviarAJuguete', ['error' => $e->getMessage()]);

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
                'success' => true,
                'message' => 'Ciclo completado exitosamente',
                'data' => $validated
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error en recibirDesdeDulces', ['error' => $e->getMessage()]);

            return response()->json([
                'error' => 'Error al procesar respuesta final',
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
        $mascota = Mascota::with('imagenes')->find($id);

        if (!$mascota) {
            return response()->json([
                'error' => 'Mascota no encontrada'
            ], 404);
        }

        return response()->json([
            'mascota' => $mascota
        ], 200);
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
            'origen' => $origen
        ]);

        return $imagen;
    }
}
