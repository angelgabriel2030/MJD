<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MascotaController;
use App\Http\Controllers\RegistrarMascota;

Route::post('/login',[MascotaController::class,'login']);
Route::put('/registrar',[RegistrarMascota::class,'registrar']);
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
