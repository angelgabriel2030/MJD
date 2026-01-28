<?php

use App\Http\Controllers\DulceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('/dulces', [DulceController::class, 'store']);
Route::get('/dulces', [DulceController::class, 'index']);
Route::get('/dulces/{id}', [DulceController::class, 'show']);
