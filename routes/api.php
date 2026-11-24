<?php

use App\Http\Controllers\RecursoController;
use Illuminate\Support\Facades\Route;

Route::pattern('id', '[0-9]+');

Route::get('/ping', fn () => ['ok' => true]);

Route::get('/recursos', [RecursoController::class, 'index']);
Route::post('/recursos', [RecursoController::class, 'store']);
Route::get('/recursos/{id}', [RecursoController::class, 'show']);
Route::patch('/recursos/{id}/consumo', [RecursoController::class, 'consumo']);
Route::delete('/recursos/{id}', [RecursoController::class, 'destroy']);