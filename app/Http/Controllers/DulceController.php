<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Dulce;
use App\Models\Imagen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DulceController extends Controller
{
    public function store(Request $request)
    {
        try {
            Log::info('Petición recibida en Dulces', $request->all());

            $validated = $request->validate([
                'mascota' => 'required|array',
                'juguete' => 'required|array',
                'dulce' => 'required|array',
                'dulce.nombre' => 'required|string',
                'dulce.sabor' => 'required|string',
            ]);

            // Extraer mascota_id si viene en el array de mascota
            $mascotaId = $validated['mascota']['id'] ?? null;

            // 1. Guardar dulce localmente
            $dulce = Dulce::create([
                'mascota_id' => $mascotaId,
                'nombre' => $validated['dulce']['nombre'],
                'sabor' => $validated['dulce']['sabor'],
                'apto_mascotas' => true,
            ]);

            // Guardar imagen del dulce si existe
            if (isset($validated['dulce']['imagen'])) {
                $dulce->imagenes()->create([
                    'url' => $validated['dulce']['imagen'],
                    'descripcion' => 'Imagen de ' . $dulce->nombre
                ]);
            }

            // 2. Cargar relaciones
            $dulce->load('imagenes');

            Log::info('Dulce guardado exitosamente', ['dulce_id' => $dulce->id]);

            // 3. RETORNAR a Laptop 2 (formato que tu compañero espera)
            return response()->json([
                'message' => 'Dulce procesado exitosamente',
                'mascota' => $validated['mascota'],
                'juguete' => $validated['juguete'],
                'dulce' => [
                    'id' => $dulce->id,
                    'nombre' => $dulce->nombre,
                    'sabor' => $dulce->sabor,
                    'apto_mascotas' => $dulce->apto_mascotas,
                    'imagenes' => $dulce->imagenes,
                    'created_at' => $dulce->created_at
                ]
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Error de validación en Dulces', ['errors' => $e->errors()]);

            return response()->json([
                'error' => 'Error de validación',
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error en servicio de dulces', ['error' => $e->getMessage()]);

            return response()->json([
                'error' => 'Error al procesar dulce',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $dulce = Dulce::with('imagenes')->find($id);

        if (!$dulce) {
            return response()->json([
                'error' => 'Dulce no encontrado'
            ], 404);
        }

        return response()->json([
            'dulce' => $dulce
        ]);
    }

    public function index()
    {
        $dulces = Dulce::with('imagenes')->get();

        return response()->json([
            'dulces' => $dulces,
            'total' => $dulces->count()
        ]);
    }
}
