<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Organization;
use App\Models\User;
use App\Models\LostItem;
use App\Models\FoundItem;
use App\Models\Claim;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DashboardController extends Controller
{
    public function index()
{
    // Get counts for dashboard
    $organizationCount = Organization::count();
    $userCount = User::count();
    $lostItemsCount = LostItem::count();
    $foundItemsCount = FoundItem::count();
    $claimCount = Claim::count(); // ✅ Total claims made
    $returnedItemsCount = LostItem::where('status', 'returned')->count();
    
    // Recent organizations
    $recentOrganizations = Organization::latest()->take(5)->get();
    
    // Monthly stats
    $monthlyStats = $this->getMonthlyStats();
    $claimStats = $this->getClaimStats();

    return view('admin.dashboard', compact(
        'organizationCount', 
        'userCount', 
        'lostItemsCount', 
        'foundItemsCount', 
        'claimCount',
        'returnedItemsCount',
        'recentOrganizations',
        'monthlyStats',
        'claimStats'
    ));
}
    
    private function getMonthlyStats()
    {
        // Get data for the last 6 months
        $months = collect([]);
        $lostItems = collect([]);
        $foundItems = collect([]);
        $returnedItems = collect([]);

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('M');
            $monthStart = now()->subMonths($i)->startOfMonth();
            $monthEnd = now()->subMonths($i)->endOfMonth();
            
            $months->push($month);
            
            $lostItems->push(LostItem::whereBetween('created_at', [$monthStart, $monthEnd])->count());
            $foundItems->push(FoundItem::whereBetween('created_at', [$monthStart, $monthEnd])->count());
            $returnedItems->push(LostItem::where('status', 'returned')
                ->whereBetween('updated_at', [$monthStart, $monthEnd])->count());
        }
        
        return [
            'months' => $months,
            'lostItems' => $lostItems,
            'foundItems' => $foundItems,
            'returnedItems' => $returnedItems
        ];
    }
    
    public function settings()
    {
        return view('admin.settings');
    }
    
    public function updateSettings(Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'current_password' => ['nullable', 'required_with:password'],
            'password' => ['nullable', 'confirmed', 'min:8'],
        ]);
        
        if ($request->password) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'The current password is incorrect.']);
            }
            
            $user->password = Hash::make($request->password);
        }
        
        $user->first_name = $request->first_name;
        $user->middle_name = $request->middle_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->save();
        
        return back()->with('success', 'Profile updated successfully!');
    }

    private function getClaimStats()
{
    $months = collect([]);
    $claims = collect([]);

    for ($i = 5; $i >= 0; $i--) {
        $month = now()->subMonths($i)->format('M');
        $monthStart = now()->subMonths($i)->startOfMonth();
        $monthEnd = now()->subMonths($i)->endOfMonth();

        $months->push($month);
        $claims->push(Claim::whereBetween('created_at', [$monthStart, $monthEnd])->count());
    }

    return [
        'months' => $months,
        'claims' => $claims
    ];
}
private function getWeeklyStats()
{
    $weeks = collect([]);
    $lostItems = collect([]);
    $foundItems = collect([]);
    $returnedItems = collect([]);

    // Last 6 weeks
    for ($i = 5; $i >= 0; $i--) {
        $weekStart = now()->subWeeks($i)->startOfWeek();
        $weekEnd = now()->subWeeks($i)->endOfWeek();

        $weeks->push("Week " . $weekStart->format('W')); // Example: Week 36

        $lostItems->push(
            LostItem::whereBetween('created_at', [$weekStart, $weekEnd])->count()
        );
        $foundItems->push(
            FoundItem::whereBetween('created_at', [$weekStart, $weekEnd])->count()
        );
        $returnedItems->push(
            LostItem::where('status', 'returned')
                ->whereBetween('updated_at', [$weekStart, $weekEnd])
                ->count()
        );
    }

    return [
        'weeks' => $weeks,
        'lostItems' => $lostItems,
        'foundItems' => $foundItems,
        'returnedItems' => $returnedItems
    ];
}


}
