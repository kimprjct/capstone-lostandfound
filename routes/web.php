<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
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

// Test route for PDF generation
Route::get('/test-pdf', function () {
    return 'PDF test route is working!';
});

// Temporary test route for PDF without auth
Route::get('/test-pdf-lost', function () {
    try {
        set_time_limit(120);
        
        $user = \App\Models\User::where('role', 'tenant')->first();
        if (!$user) {
            return 'No tenant user found';
        }
        
        auth()->login($user);
        
        $controller = new \App\Http\Controllers\Tenant\LostItemController();
        return $controller->print();
    } catch (\Exception $e) {
        return 'Error: ' . $e->getMessage() . ' at line ' . $e->getLine() . ' in ' . $e->getFile();
    }
});

// Debug route to check auth status
Route::get('/debug-auth', function () {
    $user = auth()->user();
    if (!$user) {
        return 'Not authenticated';
    }
    
    return 'Authenticated as: ' . $user->first_name . ' ' . $user->last_name . ' (Role: ' . $user->role . ', Org: ' . $user->organization_id . ')';
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// General notifications route that redirects based on user role
Route::middleware('auth')->get('/notifications', function () {
    $user = auth()->user();
    
    if ($user->role === 'admin') {
        return redirect()->route('admin.notifications.index');
    } elseif ($user->role === 'tenant') {
        return redirect()->route('tenant.notifications.index');
    } else {
        // For regular users, redirect to dashboard or show a message
        return redirect()->route('user.dashboard')->with('info', 'Notifications are not available for your role.');
    }
})->name('notifications');

// General notification API routes for all authenticated users (NO ROLE RESTRICTIONS)
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('api/notifications', [\App\Http\Controllers\API\NotificationApiController::class, 'index']);
    Route::get('api/notifications/unread-count', [\App\Http\Controllers\API\NotificationApiController::class, 'unreadCount']);
    Route::put('api/notifications/{id}/read', [\App\Http\Controllers\API\NotificationApiController::class, 'markAsRead']);
    Route::put('api/notifications/mark-all-read', [\App\Http\Controllers\API\NotificationApiController::class, 'markAllAsRead']);
    Route::get('api/notifications/organization', [\App\Http\Controllers\API\NotificationApiController::class, 'getOrganizationNotifications']);
    Route::get('api/notifications/admin', [\App\Http\Controllers\API\NotificationApiController::class, 'getAdminNotifications']);
    
    // Debug route to check authentication
    Route::get('api/debug-auth', function() {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Not authenticated'
            ]);
        }
        
        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
                'organization_id' => $user->organization_id
            ]
        ]);
    });
    
    // Test routes for notifications
    Route::post('api/test-notification', function() {
        try {
            $user = \App\Models\User::where('role', 'admin')->first();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'No admin user found']);
            }
            
            Log::info('Creating test notification for user: ' . $user->email);
            
            // Test direct notification creation
            $notification = \App\Models\Notification::create([
                'type' => 'new_item',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => $user->id,
                'organization_id' => 1,
                'title' => 'Test Notification',
                'message' => 'A user reported a found item!',
                'data' => json_encode(['test' => true]),
                'priority' => 'normal',
                'category' => 'item',
            ]);
            
            Log::info('Test notification created with ID: ' . $notification->id);
            
            return response()->json([
                'success' => true, 
                'notification_id' => $notification->id,
                'message' => 'Test notification created successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating test notification: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    });
    
    // Check notifications in database
    Route::get('api/check-notifications', function() {
        try {
            $notifications = \App\Models\Notification::all();
            $count = $notifications->count();
            
            return response()->json([
                'success' => true,
                'total_notifications' => $count,
                'notifications' => $notifications->toArray()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    });
    
    // Test route to manually mark a notification as read
    Route::put('api/test-mark-read/{id}', function($id) {
        try {
            $notification = \App\Models\Notification::find($id);
            if (!$notification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notification not found'
                ]);
            }
            
            Log::info("Test mark as read - Before: ID: {$notification->id}, is_read: {$notification->is_read}, read_at: {$notification->read_at}");
            
            // Direct database update
            $result = \App\Models\Notification::where('id', $id)->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
            
            $notification->refresh();
            
            Log::info("Test mark as read - After: ID: {$notification->id}, is_read: {$notification->is_read}, read_at: {$notification->read_at}, Update result: {$result}");
            
            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read',
                'update_result' => $result,
                'notification' => [
                    'id' => $notification->id,
                    'is_read' => $notification->is_read,
                    'read_at' => $notification->read_at
                ]
            ]);
        } catch (\Exception $e) {
            Log::error("Test mark as read error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    });
    
     // Simple test route to check if notifications are working
     Route::get('api/test-notifications', function() {
         try {
             $user = auth()->user();
             if (!$user) {
                 return response()->json([
                     'success' => false,
                     'message' => 'Not authenticated'
                 ]);
             }
             
             $notifications = \App\Models\Notification::where('notifiable_type', 'App\Models\User')
                 ->where('notifiable_id', $user->id)
                 ->orderBy('created_at', 'desc')
                 ->limit(5)
                 ->get();
             
             return response()->json([
                 'success' => true,
                 'user' => [
                     'id' => $user->id,
                     'email' => $user->email,
                     'role' => $user->role
                 ],
                 'notifications' => $notifications->toArray()
             ]);
         } catch (\Exception $e) {
             return response()->json([
                 'success' => false,
                 'message' => 'Error: ' . $e->getMessage()
             ]);
         }
     });
     
     // Test route to directly test the markAsRead API
     Route::put('api/test-mark-read-direct/{id}', function($id) {
         try {
             $user = auth()->user();
             if (!$user) {
                 return response()->json([
                     'success' => false,
                     'message' => 'Not authenticated'
                 ]);
             }
             
             Log::info("=== DIRECT TEST MARK AS READ ===");
             Log::info("User: " . $user->email . " (Role: " . $user->role . ")");
             Log::info("Notification ID: " . $id);
             
             $notification = \App\Models\Notification::find($id);
             if (!$notification) {
                 return response()->json([
                     'success' => false,
                     'message' => 'Notification not found'
                 ]);
             }
             
             Log::info("Before update - is_read: " . ($notification->is_read ? 'true' : 'false') . ", read_at: " . ($notification->read_at ? $notification->read_at : 'null'));
             
             // Direct database update
             $result = \App\Models\Notification::where('id', $id)->update([
                 'is_read' => true,
                 'read_at' => now(),
             ]);
             
             $notification->refresh();
             
             Log::info("After update - is_read: " . ($notification->is_read ? 'true' : 'false') . ", read_at: " . ($notification->read_at ? $notification->read_at : 'null') . ", Update result: " . $result);
             
             return response()->json([
                 'success' => true,
                 'message' => 'Notification marked as read',
                 'update_result' => $result,
                 'notification' => [
                     'id' => $notification->id,
                     'is_read' => $notification->is_read,
                     'read_at' => $notification->read_at
                 ]
             ]);
         } catch (\Exception $e) {
             Log::error("Direct test error: " . $e->getMessage());
             return response()->json([
                 'success' => false,
                 'message' => 'Error: ' . $e->getMessage()
             ]);
         }
     });
});

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
    
    // Lost Items Management
    Route::resource('admin/lost-items', \App\Http\Controllers\Admin\LostItemController::class);
    
    // Found Items Management  
    Route::resource('admin/found-items', \App\Http\Controllers\Admin\FoundItemController::class);
    
    // Claims Management
    Route::resource('admin/claims', \App\Http\Controllers\Admin\ClaimController::class);
    
    // Notifications
    Route::get('admin/notifications', function () {
        return view('admin.notifications.index');
    })->name('notifications.index');
    
     // Admin-specific routes only (notification API routes are in general section above)
    
});

// Tenant routes
Route::middleware(['auth', 'role:tenant'])->name('tenant.')->group(function () {
    // Dashboard
    Route::get('tenant/dashboard', [TenantDashboardController::class, 'index'])->name('dashboard');
    
    // Staff Management
    Route::resource('tenant/staff', StaffController::class);
    Route::get('/staff/{user}', [StaffController::class, 'show'])->name('tenant.staff.show');

    
    // Lost Items Management
    Route::get('tenant/lost-items/print', [TenantLostItemController::class, 'print'])->name('lost-items.print');
    Route::get('tenant/lost-items/{lost_item}/manage', [TenantLostItemController::class, 'manage'])->name('lost-items.manage');
    Route::resource('tenant/lost-items', TenantLostItemController::class);
    
    // Found Items Management
    Route::get('tenant/found-items/print', [TenantFoundItemController::class, 'print'])->name('found-items.print');
    Route::get('tenant/found-items/{found_item}/manage', [TenantFoundItemController::class, 'manage'])->name('found-items.manage');
    Route::resource('tenant/found-items', TenantFoundItemController::class);
    
    // Claims Management
    Route::resource('tenant/claims', TenantClaimController::class);
    Route::get('tenant/claims/{claim}/review', [TenantClaimController::class, 'review'])->name('claims.review');
    Route::post('tenant/claims/{claim}/approve', [TenantClaimController::class, 'approve'])->name('claims.approve');
    Route::post('tenant/claims/{claim}/reject', [TenantClaimController::class, 'reject'])->name('claims.reject');
    
    // Settings
    Route::get('tenant/settings', [TenantDashboardController::class, 'settings'])->name('settings');
    Route::post('tenant/settings', [TenantDashboardController::class, 'updateSettings'])->name('settings.update');
    
    // Notifications
    Route::get('tenant/notifications', function () {
        return view('tenant.notifications.index');
    })->name('notifications.index');
    
     // Tenant-specific routes only (notification API routes are in general section above)
    
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
    
     // User-specific routes only (notification API routes are in general section above)
    
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
