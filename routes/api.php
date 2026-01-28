<?php

use App\Http\Controllers\JuguetesController;
use Illuminate\Support\Facades\Route;

Route::middleware(['validate.partner.ip'])->group(function () {
    Route::post('/juguetes/desde-mascota', [JuguetesController::class, 'recibirDesdeMascota']);
    Route::post('/juguetes/desde-dulces', [JuguetesController::class, 'recibirDesdeDulces']);
    Route::post('/juguetes/enviar-a-dulces', [JuguetesController::class, 'enviarADulces']);
Route::post('/juguetes/enviar', [JuguetesController::class, 'enviarSoloJuguete']);
    
});


Route::get('/juguetes', [JuguetesController::class, 'index']);
Route::get('/juguetes/{id}', [JuguetesController::class, 'show']);
Route::get('/imagenes', [JuguetesController::class, 'listarImagenes']);