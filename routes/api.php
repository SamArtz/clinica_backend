<?php

use App\Http\Controllers\Api\AppointmentController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

Route::post('/login', [AuthController::class, 'login']);

// Opción correcta:
Route::middleware('auth:sanctum')->post('/appointments', [AppointmentController::class, 'store']);