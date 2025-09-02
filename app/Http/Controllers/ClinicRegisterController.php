<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Clinic;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class ClinicRegisterController extends Controller
{
    // Step 1: Show clinic info form
    public function showStepOneForm()
    {
        return view('admin.clinic_register_step1');
    }

    public function postStepOne(Request $request)
    {
        $validated = $request->validate([
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'contact_number' => 'required|string|max:20',
        ]);

        if ($request->hasFile('profile_picture')) {
            $path = $request->file('profile_picture')->store('clinic_profiles', 'public');
            $validated['profile_picture'] = $path;
        }

        Session::put('clinic_data_step1', $validated);

        return redirect()->route('clinic.register.step2');
    }

    // Step 2: Account creation
    public function showStepTwoForm()
    {
        return view('admin.clinic_register_step2');
    }

    public function postStepTwo(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255|unique:users,email|unique:clinics,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $step1 = Session::get('clinic_data_step1');

        if (!$step1) {
            return redirect()->route('clinic.register.step1')->withErrors(['session' => 'Please complete step 1 first.']);
        }

        $clinic = Clinic::create([
            'logo' => $step1['profile_picture'] ?? null,
            'name' => $step1['name'],
            'address' => $step1['address'],
            'contact_number' => $step1['contact_number'],
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        User::create([
            'first_name' => $clinic->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'clinic',
        ]);

        Session::forget('clinic_data_step1');

        return redirect()->route('login')->with('success', 'Clinic registered! You can now log in.');
    }

    // Optional login route for clinics
    public function clinicLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            if ($user->role === 'clinic') {
                return redirect()->route('clinic.dashboard');
            } else {
                Auth::logout();
                return back()->withErrors(['email' => 'Not authorized as a clinic.']);
            }
        }

        return back()->withErrors(['email' => 'Invalid login credentials.']);
    }
}
