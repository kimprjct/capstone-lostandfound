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
use App\Http\Controllers\User\FoundItemController as UserFoundItemController;
use App\Http\Controllers\User\ClaimController as UserClaimController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Public routes
Route::get('/', function () {
    return view('auth.login');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Organizations listing
Route::get('/organizations', [App\Http\Controllers\OrganizationController::class, 'index'])->name('organizations.index');
Route::get('/organizations/{organization}', [App\Http\Controllers\OrganizationController::class, 'show'])->name('organizations.show');

// Admin routes
Route::middleware(['auth', 'role:admin'])->name('admin.')->group(function () {
    // Dashboard
    Route::get('admin/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    
    // Organization Management
    Route::resource('admin/organizations', OrganizationController::class);
    
    // User Management
    Route::resource('admin/users', UserController::class);
    
    // Settings
    Route::get('admin/settings', [AdminDashboardController::class, 'settings'])->name('settings');
    Route::post('admin/settings', [AdminDashboardController::class, 'updateSettings'])->name('settings.update');
});

// Tenant routes
Route::middleware(['auth', 'role:tenant'])->name('tenant.')->group(function () {
    // Dashboard
    Route::get('tenant/dashboard', [TenantDashboardController::class, 'index'])->name('dashboard');
    
    // Staff Management
    Route::resource('tenant/staff', StaffController::class);
    
    // Lost Items Management
    Route::resource('tenant/lost-items', TenantLostItemController::class);
    
    // Found Items Management
    Route::resource('tenant/found-items', TenantFoundItemController::class);
    
    // Claims Management
    Route::resource('tenant/claims', TenantClaimController::class);
    Route::post('tenant/claims/{claim}/approve', [TenantClaimController::class, 'approve'])->name('claims.approve');
    Route::post('tenant/claims/{claim}/reject', [TenantClaimController::class, 'reject'])->name('claims.reject');
    
    // Settings
    Route::get('tenant/settings', [TenantDashboardController::class, 'settings'])->name('settings');
    Route::post('tenant/settings', [TenantDashboardController::class, 'updateSettings'])->name('settings.update');
});

// User routes
Route::middleware(['auth', 'role:user'])->name('user.')->group(function () {
    // Dashboard
    Route::get('user/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');
    
    // Lost Item Reporting
    Route::resource('user/lost-items', UserLostItemController::class);
    
    // Found Item Claiming
    Route::get('user/found-items', [UserFoundItemController::class, 'index'])->name('found-items.index');
    Route::get('user/found-items/{foundItem}', [UserFoundItemController::class, 'show'])->name('found-items.show');
    Route::post('user/found-items/{foundItem}/claim', [UserFoundItemController::class, 'claim'])->name('found-items.claim');
    
    // Claims
    Route::get('user/claims', [UserClaimController::class, 'index'])->name('claims.index');
    Route::get('user/claims/{claim}', [UserClaimController::class, 'show'])->name('claims.show');
    
    // Profile Settings
    Route::get('user/profile', [UserDashboardController::class, 'profile'])->name('profile');
    Route::put('user/profile', [UserDashboardController::class, 'updateProfile'])->name('profile.update');
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
