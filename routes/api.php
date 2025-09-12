<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\API\OrganizationApiController;
use App\Http\Controllers\API\AppointmentApiController;
use App\Http\Controllers\API\FoundItemApiController;

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

//org
Route::get('/organizations', [OrganizationApiController::class, 'index']);
Route::get('/organizations/{id}', [OrganizationApiController::class, 'show']);

//found items
Route::get('/found-items/organizations', [FoundItemApiController::class, 'getOrganizations']);

// New route to get found items by organization (publicly accessible)
Route::get('/found-items/organization/{organizationId}', [FoundItemApiController::class, 'getByOrganization']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/found-items', [FoundItemApiController::class, 'index']);
    Route::post('/found-items', [FoundItemApiController::class, 'store']);
    Route::get('/found-items/{id}', [FoundItemApiController::class, 'show']);
    Route::put('/found-items/{id}', [FoundItemApiController::class, 'update']);
    Route::delete('/found-items/{id}', [FoundItemApiController::class, 'destroy']);
});
