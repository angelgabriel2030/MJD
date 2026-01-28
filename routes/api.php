<?php

use App\Http\Controllers\JugueteController;
use Illuminate\Support\Facades\Route;

Route::middleware(['validate.partner.ip'])->group(function () {
    
    Route::post('/juguetes/desde-mascota', [JuguetesController::class, 'recibirDesdeMascota']);
    Route::post('/juguetes/desde-dulces', [JuguetesController::class, 'recibirDesdeDulces']);
    
});

Route::get('/juguetes', [JuguetesController::class, 'index']);
Route::get('/juguetes/{id}', [JuguetesController::class, 'show']);
Route::get('/imagenes', [JuguetesController::class, 'listarImagenes']);