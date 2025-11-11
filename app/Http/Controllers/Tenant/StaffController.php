<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;

class StaffController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        if (!$user || !$user->organization) {
            return redirect()->route('login')
                ->with('error', 'You must be associated with an organization to access this feature.');
        }
    
        $organization = $user->organization;
    
        // Users explicitly part of the organization (UserTypeID: 2=Tenant, 3=User)
        $staffMembersQuery = User::where('organization_id', $organization->id)
            ->whereIn('UserTypeID', [2, 3]);
    
        // Users who reported Lost Items
        $lostReporters = User::whereHas('lostItems', function ($query) use ($organization) {
            $query->where('organization_id', $organization->id);
        });
    
        // Users who reported Found Items
        $foundReporters = User::whereHas('foundItems', function ($query) use ($organization) {
            $query->where('organization_id', $organization->id);
        });
    
        // ✅ Users who filed Claims
        $claimants = User::whereHas('claims', function ($query) use ($organization) {
            $query->where('organization_id', $organization->id);
        });
    
        // Merge all users
        $staffMembers = $staffMembersQuery
            ->union($lostReporters)
            ->union($foundReporters)
            ->union($claimants) // ✅ include claimants
            ->latest()
            ->paginate(10);
    
        return view('tenant.staff.index', compact('staffMembers', 'organization'));
    }
    

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();
        if (!$user || !$user->organization) {
            return redirect()->route('login')
                ->with('error', 'You must be associated with an organization to access this feature.');
        }

        return view('tenant.staff.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $authUser = Auth::user();
        if (!$authUser || !$authUser->organization) {
            return redirect()->route('login')
                ->with('error', 'You must be associated with an organization to access this feature.');
        }

        $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = new User();
        $user->first_name = $request->first_name;
        $user->middle_name = $request->middle_name;
        $user->last_name = $request->last_name;
        $user->address = $request->address;
        $user->phone_number = $request->phone_number;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->UserTypeID = 2; // Tenant
        $user->organization_id = $authUser->organization_id;
        $user->save();

        return redirect()->route('tenant.staff.index')
            ->with('success', 'Staff member created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $authUser = Auth::user();
    
        if (!$authUser || !$authUser->organization) {
            return redirect()->route('login')
                ->with('error', 'You must be associated with an organization to access this feature.');
        }
    
        $organization = $authUser->organization;
    
        // ✅ Eager load claims with lostItem + foundItem + reporters, filtered by organization
        $user = User::with([
            'claims' => function ($query) use ($organization) {
                $query->where('organization_id', $organization->id);
            },
            'claims.lostItem.user',
            'claims.foundItem.user',
            'lostItems' => function ($query) use ($organization) {
                $query->where('organization_id', $organization->id);
            },
            'foundItems' => function ($query) use ($organization) {
                $query->where('organization_id', $organization->id);
            }
        ])->findOrFail($id);
    
        $belongsToOrg = $user->organization_id === $organization->id;
        $reportedLost = $user->lostItems()->where('organization_id', $organization->id)->exists();
        $reportedFound = $user->foundItems()->where('organization_id', $organization->id)->exists();
        $filedClaim = $user->claims()->where('organization_id', $organization->id)->exists();
    
        if (!($belongsToOrg || $reportedLost || $reportedFound || $filedClaim)) {
            abort(403, 'Unauthorized access');
        }
    
        return view('tenant.staff.show', compact('user'));
    }
    


    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $authUser = Auth::user();
        if (!$authUser || !$authUser->organization) {
            return redirect()->route('login')
                ->with('error', 'You must be associated with an organization to access this feature.');
        }

        $user = User::findOrFail($id);

        if ($user->organization_id !== $authUser->organization_id) {
            return redirect()->route('tenant.staff.index')
                ->with('error', 'Unauthorized access.');
        }

        return view('tenant.staff.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $authUser = Auth::user();
        if (!$authUser || !$authUser->organization) {
            return redirect()->route('login')
                ->with('error', 'You must be associated with an organization to access this feature.');
        }

        $user = User::findOrFail($id);

        if ($user->organization_id !== $authUser->organization_id) {
            return redirect()->route('tenant.staff.index')
                ->with('error', 'Unauthorized access.');
        }

        $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        ]);

        // Handle password update
        if ($request->filled('password')) {
            $request->validate([
                'current_password' => 'required|string',
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ]);

            // Verify current password
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'The current password you entered is incorrect.']);
            }

            $user->password = Hash::make($request->password);
        }

        // Update other fields
        $user->first_name = $request->first_name;
        $user->middle_name = $request->middle_name;
        $user->last_name = $request->last_name;
        $user->address = $request->address;
        $user->phone_number = $request->phone_number;
        $user->email = $request->email;
        $user->save();

        return redirect()->route('tenant.staff.index')
            ->with('success', 'Admin account updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $authUser = Auth::user();
        if (!$authUser || !$authUser->organization) {
            return redirect()->route('login')
                ->with('error', 'You must be associated with an organization to access this feature.');
        }

        $user = User::findOrFail($id);

        if ($user->organization_id !== $authUser->organization_id) {
            return redirect()->route('tenant.staff.index')
                ->with('error', 'Unauthorized access.');
        }

        $user->delete();

        return redirect()->route('tenant.staff.index')
            ->with('success', 'Admin account removed successfully.');
    }
}
