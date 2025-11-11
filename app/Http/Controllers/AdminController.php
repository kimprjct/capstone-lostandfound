<?php

namespace App\Http\Controllers;

require_once base_path('/resources/libs/dompdf/autoload.inc.php');
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Organization;
use Illuminate\Support\Facades\Storage;
use Dompdf\Dompdf;
use Dompdf\Options;






class AdminController extends Controller
{
    // Admin dashboard
    public function dashboard()
    {
        return view('admin.dashboard');
    }

    // Show only regular users (not admin or clinic accounts)
    public function usermag()
    {
        $users = User::where('role', 'user')->get();
        return view('admin.usermag', compact('users'));
    }

    // Show settings form
    public function settings()
    {
        $admin = Auth::user(); // Get currently logged-in admin
        return view('admin.settings', compact('admin'));
    }

// Update admin account details
public function updateSettings(Request $request)
{
    $request->validate([
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'email' => 'required|email|max:255|unique:users,email,' . Auth::id(),
        'password' => 'nullable|min:6|confirmed', // password_confirmation field required
    ]);

    /** @var User $admin */
    $admin = Auth::user();
    $admin->first_name = $request->first_name;
    $admin->last_name = $request->last_name;
    $admin->email = $request->email;

    if ($request->filled('password')) {
        $admin->password = Hash::make($request->password);
    }

    $admin->save();

    return redirect()->back()->with('success', 'Settings updated successfully.');
}
    // Show all registered organizations
public function organizationList()
{
    $organizations = Organization::with('users')->get(); // eager load related user info
    return view('admin.organizations.list', compact('organizations'));
}

// View a specific organization by ID
public function viewOrganization($id)
{
    $organization = Organization::with('users')->findOrFail($id);
    return view('admin.organizations.view', compact('organization'));
}

public function updateOrganizationDetails(Request $request, $id)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'address' => 'required|string|max:255',
        'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
    ]);

    /** @var Organization $organization */
    $organization = Organization::findOrFail($id);

    $organization->name = $request->name;
    $organization->address = $request->address;

    if ($request->hasFile('logo')) {
        if ($organization->logo) Storage::delete('public/' . $organization->logo);
        $organization->logo = $request->file('logo')->store('organization_logos', 'public');
    }

    $organization->save();

    return back()->with('success', 'Organization details updated successfully.');
}

public function updateOrganizationAccount(Request $request, $userId)
{
    /** @var User $user */
    $user = User::findOrFail($userId);

    if ($user->role !== 'tenant') {
        return redirect()->back()->withErrors(['error' => 'This user is not a tenant account.']);
    }

    $request->validate([
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'email' => 'required|email|max:255|unique:users,email,' . $userId,
        'password' => 'nullable|min:6|confirmed',
    ]);

    $user->first_name = $request->first_name;
    $user->last_name = $request->last_name;
    $user->email = $request->email;

    if ($request->filled('password')) {
        $user->password = Hash::make($request->password);
    }

    $user->save();

    return back()->with('success', 'Organization account updated successfully.');
}
public function downloadOrganizationInfo($id)
{
    $organization = Organization::with('users')->findOrFail($id);

    // Render a Blade view to HTML
    $html = view('admin.organizations.pdf', compact('organization'))->render();

    // DOMPDF setup
    $options = new Options();
    $options->set('defaultFont', 'Arial');

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Output the PDF
    return response($dompdf->output(), 200)
        ->header('Content-Type', 'application/pdf')
        ->header('Content-Disposition', 'attachment; filename="organization_info.pdf"');
}

public function getUserStats($type)
{
    if ($type === 'month') {
        $userStats = \App\Models\User::where('role', 'user')
            ->selectRaw('MONTH(created_at) as label, COUNT(*) as total')
            ->groupBy('label')
            ->orderBy('label')
            ->pluck('total', 'label');

        $tenantStats = \App\Models\User::where('role', 'tenant')
            ->selectRaw('MONTH(created_at) as label, COUNT(*) as total')
            ->groupBy('label')
            ->orderBy('label')
            ->pluck('total', 'label');

        $labels = collect(range(1, 12))->map(function ($m) {
            return date("F", mktime(0, 0, 0, $m, 1));
        });
    } else { // week
        $userStats = \App\Models\User::where('role', 'user')
            ->selectRaw('WEEK(created_at) as label, COUNT(*) as total')
            ->groupBy('label')
            ->orderBy('label')
            ->pluck('total', 'label');

        $tenantStats = \App\Models\User::where('role', 'tenant')
            ->selectRaw('WEEK(created_at) as label, COUNT(*) as total')
            ->groupBy('label')
            ->orderBy('label')
            ->pluck('total', 'label');

        $maxWeek = now()->endOfYear()->weekOfYear;
        $labels = collect(range(1, $maxWeek))->map(fn($w) => "Week $w");
    }

    // Normalize data (fill missing months/weeks with 0)
    $usersData = [];
    $tenantsData = [];
    foreach ($labels as $i => $label) {
        $usersData[] = $userStats->get($i + 1, 0);
        $tenantsData[] = $tenantStats->get($i + 1, 0);
    }

    return response()->json([
        'labels' => $labels,
        'users' => $usersData,
        'tenants' => $tenantsData,
    ]);
}

// Delete a user
public function deleteUser($id)
{
    $user = User::findOrFail($id);

    if ($user->role === 'admin') {
        return redirect()->back()->withErrors(['error' => 'Cannot delete an admin account.']);
    }

    $user->delete();
    return redirect()->route('admin.usermag')->with('success', 'User deleted successfully.');
}

// Show edit form
public function editUser($id)
{
    $user = User::findOrFail($id);
    return view('admin.users.edit', compact('user'));
}

// Update user
public function updateUser(Request $request, $id)
{
    /** @var User $user */
    $user = User::findOrFail($id);

    $request->validate([
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'email' => 'required|email|max:255|unique:users,email,' . $id,
        'password' => 'nullable|min:6|confirmed',
    ]);

    $user->first_name = $request->first_name;
    $user->last_name = $request->last_name;
    $user->email = $request->email;

    if ($request->filled('password')) {
        $user->password = Hash::make($request->password);
    }

    $user->save();

    return redirect()->route('admin.usermag')->with('success', 'User updated successfully.');
}


}
