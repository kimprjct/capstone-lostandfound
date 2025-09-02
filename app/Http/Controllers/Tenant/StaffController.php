<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;

class StaffController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        $user = Auth::user();
        if (!$user || !$user->organization) {
            return redirect()->route('login')
                ->with('error', 'You must be associated with an organization to access this feature.');
        }
        
        $organization = $user->organization;
        $staffMembers = User::where('organization_id', $organization->id)
                          ->where('role', 'tenant')
                          ->latest()
                          ->paginate(10);
        
        return view('tenant.staff.index', compact('staffMembers', 'organization'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
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
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
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
        $user->role = 'tenant';
        $user->organization_id = $authUser->organization_id;
        $user->save();

        return redirect()->route('tenant.staff.index')
            ->with('success', 'Staff member created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show($id)
    {
        $authUser = Auth::user();
        if (!$authUser || !$authUser->organization) {
            return redirect()->route('login')
                ->with('error', 'You must be associated with an organization to access this feature.');
        }
        
        $user = User::findOrFail($id);
        
        // Check if the user belongs to the tenant's organization
        if ($user->organization_id !== $authUser->organization_id) {
            return redirect()->route('tenant.staff.index')
                ->with('error', 'Unauthorized access.');
        }
        
        return view('tenant.staff.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit($id)
    {
        $authUser = Auth::user();
        if (!$authUser || !$authUser->organization) {
            return redirect()->route('login')
                ->with('error', 'You must be associated with an organization to access this feature.');
        }
        
        $user = User::findOrFail($id);
        
        // Check if the user belongs to the tenant's organization
        if ($user->organization_id !== $authUser->organization_id) {
            return redirect()->route('tenant.staff.index')
                ->with('error', 'Unauthorized access.');
        }
        
        return view('tenant.staff.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $authUser = Auth::user();
        if (!$authUser || !$authUser->organization) {
            return redirect()->route('login')
                ->with('error', 'You must be associated with an organization to access this feature.');
        }
        
        $user = User::findOrFail($id);
        
        // Check if the user belongs to the tenant's organization
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

        // Update password only if provided
        if ($request->filled('password')) {
            $request->validate([
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ]);
            $user->password = Hash::make($request->password);
        }

        $user->first_name = $request->first_name;
        $user->middle_name = $request->middle_name;
        $user->last_name = $request->last_name;
        $user->address = $request->address;
        $user->phone_number = $request->phone_number;
        $user->email = $request->email;
        $user->save();

        return redirect()->route('tenant.staff.index')
            ->with('success', 'Staff information updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $authUser = Auth::user();
        if (!$authUser || !$authUser->organization) {
            return redirect()->route('login')
                ->with('error', 'You must be associated with an organization to access this feature.');
        }
        
        $user = User::findOrFail($id);
        
        // Check if the user belongs to the tenant's organization
        if ($user->organization_id !== $authUser->organization_id) {
            return redirect()->route('tenant.staff.index')
                ->with('error', 'Unauthorized access.');
        }
        
        $user->delete();
        
        return redirect()->route('tenant.staff.index')
            ->with('success', 'Staff member removed successfully.');
    }
}
