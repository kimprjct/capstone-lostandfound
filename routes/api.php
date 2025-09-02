<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ClinicInfoController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ClinicController;
use App\Http\Controllers\Api\ClinicGalleryController;
use App\Http\Controllers\Api\ClinicFieldApiController;
use App\Http\Controllers\Api\ClinicAppointmentApiController;
use App\Http\Controllers\Api\ClinicHomepageApiController;   
use App\Http\Controllers\API\AppointmentApiController;
use App\Http\Controllers\API\ClinicStatusController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [ProfileController::class, 'show']);
    Route::put('/me', [ProfileController::class, 'update']);
});

Route::get('/clinics/{clinicId}/appointments', [AppointmentApiController::class, 'index']);
Route::get('/clinics/{clinicId}/appointments/{id}', [AppointmentApiController::class, 'show']);
Route::put('/clinics/{clinicId}/appointments/{id}/status', [AppointmentApiController::class, 'updateStatus']);



//extra

// Route::middleware('auth:sanctum')->get('/me', MeController::class);

Route::get('/user', [AuthController::class, 'user']);


Route::middleware('auth:sanctum')->put('/user', [UserController::class, 'update']);

Route::middleware('auth:sanctum')->put('/update-profile', [AuthController::class, 'updateProfile']);

