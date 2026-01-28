<?php

use App\Http\Controllers\JugueteController;
use Illuminate\Support\Facades\Route;

Route::middleware(['validate.partner.ip'])->group(function () {
    
    Route::post('/juguetes/desde-mascota', [JugueteController::class, 'recibirDesdeMascota']);
    Route::post('/juguetes/desde-dulces', [JugueteController::class, 'recibirDesdeDulces']);
    
});

Route::get('/juguetes', [JugueteController::class, 'index']);
Route::get('/juguetes/{id}', [JugueteController::class, 'show']);
Route::get('/imagenes', [JugueteController::class, 'listarImagenes']);