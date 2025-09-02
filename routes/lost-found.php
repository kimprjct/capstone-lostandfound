<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\OrganizationController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Tenant\DashboardController as TenantDashboardController;
use App\Http\Controllers\Tenant\StaffController;
use App\Http\Controllers\Tenant\LostItemController as TenantLostItemController;
use App\Http\Controllers\Tenant\FoundItemController as TenantFoundItemController;
use App\Http\Controllers\Tenant\ClaimController as TenantClaimController;
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\User\LostItemController as UserLostItemController;
use App\Http\Controllers\User\ClaimController as UserClaimController;

// Public routes
Route::get('/', function () {
    return view('auth.login');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Admin routes
Route::prefix('admin')->middleware(['auth', 'role:admin'])->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    
    // Organization Management
    Route::resource('/organizations', OrganizationController::class);
    
    // User Management
    Route::resource('/users', UserController::class);
    
    // Settings
    Route::get('/settings', [AdminDashboardController::class, 'settings'])->name('settings');
    Route::post('/settings', [AdminDashboardController::class, 'updateSettings'])->name('settings.update');
});

// Tenant routes
Route::prefix('tenant')->middleware(['auth', 'role:tenant'])->name('tenant.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [TenantDashboardController::class, 'index'])->name('dashboard');
    
    // Staff Management
    Route::resource('/staff', StaffController::class);
    
    // Lost Items Management
    Route::resource('/lost-items', TenantLostItemController::class);
    
    // Found Items Management
    Route::resource('/found-items', TenantFoundItemController::class);
    
    // Claims Management
    Route::resource('/claims', TenantClaimController::class);
    Route::post('/claims/{claim}/approve', [TenantClaimController::class, 'approve'])->name('claims.approve');
    Route::post('/claims/{claim}/reject', [TenantClaimController::class, 'reject'])->name('claims.reject');
});

// User routes
Route::prefix('user')->middleware(['auth', 'role:user'])->name('user.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');
    
    // Lost Items Reporting
    Route::resource('/lost-items', UserLostItemController::class);
    
    // Claims Management
    Route::resource('/claims', UserClaimController::class);
});

// Redirect based on role after login
Route::get('/home', function() {
    if (auth()->check()) {
        if (auth()->user()->role == 'admin') {
            return redirect()->route('admin.dashboard');
        } else if (auth()->user()->role == 'tenant') {
            return redirect()->route('tenant.dashboard');
        } else {
            return redirect()->route('user.dashboard');
        }
    }
    return redirect()->route('login');
})->name('home');
