<?php

use App\Http\Middleware\validarIp;
use App\Http\Controllers\JuguetesController;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['validate.partner.ip'])->group(function () {
    Route::post('/juguetes/desde-mascota', [JuguetesController::class, 'recibirDesdeMascota']);
    Route::post('/juguetes/desde-dulces', [JuguetesController::class, 'recibirDesdeDulces']);
    Route::post('/juguetes/enviar-a-dulces', [JuguetesController::class, 'enviarADulces']);
Route::post('/juguetes/enviar', [JuguetesController::class, 'enviarSoloJuguete']);
    
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/juguetes/desde-mascota', [JuguetesController::class, 'recibirDesdeMascota']);
    Route::post('/juguetes/desde-dulces', [JuguetesController::class, 'recibirDesdeDulces']);
    Route::post('/juguetes/enviar', [JuguetesController::class, 'enviarSoloJuguete']);
    Route::get('/juguetes', [JuguetesController::class, 'index']);
    Route::get('/juguetes/{id}', [JuguetesController::class, 'show']);
    Route::get('/imagenes', [JuguetesController::class, 'listarImagenes']);
});


Route::get('/juguetes', [JuguetesController::class, 'index']);
Route::get('/juguetes/{id}', [JuguetesController::class, 'show']);
Route::get('/imagenes', [JuguetesController::class, 'listarImagenes']);
Route::post('/create', [AuthController::class, 'create']);
Route::post('/login', [AuthController::class, 'login']);