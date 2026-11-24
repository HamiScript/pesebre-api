<?php

use App\Exceptions\ApiException;
use Illuminate\Support\Facades\Route;

Route::get('/ping', fn () => ['ok' => true]);
Route::get('/boom', fn () => throw new ApiException(422, 'PRUEBA', 'Error de prueba'));