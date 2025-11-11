<?php

// app/Http/Controllers/Step2Controller.php

namespace App\Http\Controllers;

use App\Models\ClinicInfo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class Step2Controller extends Controller
{
    public function create()
    {
        // Ensure clinic_info is set
        if (!session()->has('clinic_info')) {
            return redirect()->route('step1.create')->withErrors(['message' => 'Please complete step 1 first.']);
        }

        return view('admin.step2');
    }

public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
        'password' => 'required|string|confirmed|min:6',
    ]);

    $clinicData = session('clinic_info');

    // Create user with clinic role (not logging them in)
    User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'UserTypeID' => 2, // Clinic maps to Tenant (UserTypeID = 2)
    ]);

    // Save clinic info
    ClinicInfo::create([
        'user_id' => User::latest()->first()->id,
        'clinic_name' => $clinicData['clinic_name'],
        'address' => $clinicData['address'],
        'contact_number' => $clinicData['contact_number'],
        'profile_picture' => $clinicData['logo'],
    ]);

    // Clear session data
    session()->forget('clinic_info');

    // Redirect with success message only
    return redirect()->route('step2.create')->with('success', 'Clinic account successfully added.');
}

}
