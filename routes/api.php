<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MascotaController;
use App\Http\Controllers\RegistrarMascota;
use App\Http\Controllers\UserController;

Route::middleware(['validate.partner.ip'])->group(function () {
    Route::post('/mascotas/desde-juguete', [MascotaController::class, 'recibirDesdeJuguete']);
    Route::post('/mascotas/desde-dulces', [MascotaController::class, 'recibirDesdeDulces']);
    Route::post('/mascotas/enviar-a-juguete', [MascotaController::class, 'enviarAJuguete']);
    Route::post('/mascotas/enviar', [MascotaController::class, 'enviarSoloMascota']);
});

Route::post('/login', [UserController::class, 'login']);
Route::get('/mascotas', [MascotaController::class, 'index']);
Route::get('/mascotas/{id}', [MascotaController::class, 'show']);
Route::get('/mascotas/imagenes', [MascotaController::class, 'listarImagenes']);
Route::post('/mascotas/login', [MascotaController::class, 'login']);
