<?php

namespace App\Http\Controllers;

require_once base_path('/resources/libs/dompdf/autoload.inc.php');
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\ClinicInfo;
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
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . Auth::id(),
            'password' => 'nullable|min:6|confirmed', // password_confirmation field required
        ]);

        $admin = Auth::user();
        $admin->name = $request->name;
        $admin->email = $request->email;

        if ($request->filled('password')) {
            $admin->password = Hash::make($request->password);
        }

        $admin->save();

        return redirect()->back()->with('success', 'Settings updated successfully.');
    }
    // Show all registered clinics
public function clinicList()
{
    $clinics = ClinicInfo::with('user')->get(); // eager load related user info
    return view('admin.clinics.list', compact('clinics'));
}

// View a specific clinic by ID
public function viewClinic($id)
{
    $clinic = ClinicInfo::with('user')->findOrFail($id);
    return view('admin.clinics.view', compact('clinic'));
}
// public function updateClinicPassword(Request $request, $id)
// {
//     $request->validate([
//         'password' => 'required|string|min:6|confirmed',
//     ]);

//     $user = User::findOrFail($id);

//     if ($user->role !== 'clinic') {
//         return redirect()->back()->withErrors(['error' => 'This user is not a clinic account.']);
//     }

//     $user->password = Hash::make($request->password);
//     $user->save();

//     return redirect()->back()->with('password_updated', 'Clinic account password updated successfully.');
// }

public function updateClinicDetails(Request $request, $id)
{
    $request->validate([
        'clinic_name' => 'required|string|max:255',
        'address' => 'required|string|max:255',
        'contact_number' => 'required|string|max:20',
        'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
    ]);

    $clinic = ClinicInfo::findOrFail($id);

    $clinic->clinic_name = $request->clinic_name;
    $clinic->address = $request->address;
    $clinic->contact_number = $request->contact_number;


    if ($request->hasFile('profile_picture')) {
        if ($clinic->profile_picture) Storage::delete('public/' . $clinic->profile_picture);
        $clinic->profile_picture = $request->file('profile_picture')->store('profile_pictures', 'public');
    }

    $clinic->save();

    return back()->with('success', 'Clinic details updated successfully.');
}

public function updateClinicAccount(Request $request, $userId)
{
    $user = User::findOrFail($userId);

    if ($user->role !== 'clinic') {
        return redirect()->back()->withErrors(['error' => 'This user is not a clinic account.']);
    }

    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255|unique:users,email,' . $userId,
        'password' => 'nullable|min:6|confirmed',
    ]);

    $user->name = $request->name;
    $user->email = $request->email;

    if ($request->filled('password')) {
        $user->password = Hash::make($request->password);
    }

    $user->save();

    return back()->with('success', 'Clinic account updated successfully.');
}
public function downloadClinicInfo($id)
{
    $clinic = ClinicInfo::with('user')->findOrFail($id);

    // Render a Blade view to HTML
    $html = view('admin.clinics.pdf', compact('clinic'))->render();

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
        ->header('Content-Disposition', 'attachment; filename="clinic_info.pdf"');
}

public function getUserStats($type)
{
    if ($type === 'month') {
        $userStats = \App\Models\User::where('role', 'user')
            ->selectRaw('MONTH(created_at) as label, COUNT(*) as total')
            ->groupBy('label')
            ->orderBy('label')
            ->pluck('total', 'label');

        $clinicStats = \App\Models\User::where('role', 'clinic')
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

        $clinicStats = \App\Models\User::where('role', 'clinic')
            ->selectRaw('WEEK(created_at) as label, COUNT(*) as total')
            ->groupBy('label')
            ->orderBy('label')
            ->pluck('total', 'label');

        $maxWeek = now()->endOfYear()->weekOfYear;
        $labels = collect(range(1, $maxWeek))->map(fn($w) => "Week $w");
    }

    // Normalize data (fill missing months/weeks with 0)
    $usersData = [];
    $clinicsData = [];
    foreach ($labels as $i => $label) {
        $usersData[] = $userStats->get($i + 1, 0);
        $clinicsData[] = $clinicStats->get($i + 1, 0);
    }

    return response()->json([
        'labels' => $labels,
        'users' => $usersData,
        'clinics' => $clinicsData,
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
    $user = User::findOrFail($id);

    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255|unique:users,email,' . $id,
        'password' => 'nullable|min:6|confirmed',
    ]);

    $user->name = $request->name;
    $user->email = $request->email;

    if ($request->filled('password')) {
        $user->password = Hash::make($request->password);
    }

    $user->save();

    return redirect()->route('admin.usermag')->with('success', 'User updated successfully.');
}


}
