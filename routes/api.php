<?php

use App\Http\Controllers\AnimalController;
use App\Http\Controllers\RecursoController;
use App\Http\Middleware\RolRequerido;
use Illuminate\Support\Facades\Route;

Route::pattern('id', '[0-9]+');

Route::get('/ping', fn () => ['ok' => true]);

// Públicas
Route::get('/recursos', [RecursoController::class, 'index']);
Route::get('/recursos/{id}', [RecursoController::class, 'show']);
Route::get('/animales', [AnimalController::class, 'index']);
Route::post('/animales', [AnimalController::class, 'store']);
Route::patch('/animales/{id}/alimentacion', [AnimalController::class, 'alimentacion']);

// Solo ENCARGADO_PESEBRE
Route::middleware(RolRequerido::class.':ENCARGADO_PESEBRE')->group(function () {
    Route::post('/recursos', [RecursoController::class, 'store']);
    Route::patch('/recursos/{id}/consumo', [RecursoController::class, 'consumo']);
    Route::delete('/recursos/{id}', [RecursoController::class, 'destroy']);
    Route::patch('/recursos/{id}/reabastecimiento', [RecursoController::class, 'reabastecimiento']);
});
