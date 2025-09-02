<?php

namespace App\Http\Controllers\Clinic;

use App\Http\Controllers\Controller;
use App\Models\ClinicInfo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    /**
     * Show the settings page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();
        $clinic = ClinicInfo::where('user_id', $user->id)->first();
        
        return view('clinic.settings', compact('user', 'clinic'));
    }

    /**
     * Update the clinic profile information.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $clinic = ClinicInfo::where('user_id', $user->id)->first();

        $request->validate([
            'name' => 'required|string|max:255',
            'clinic_name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'contact_number' => 'required|string|max:20',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($user->id),
            ],
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Update user information
        $user->name = $request->name;
        $user->email = $request->email;
        $user->save();

        // Update clinic information
        $clinic->clinic_name = $request->clinic_name;
        $clinic->address = $request->address;
        $clinic->contact_number = $request->contact_number;

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            // Delete old profile picture if exists
            if ($clinic->profile_picture) {
                Storage::disk('public')->delete($clinic->profile_picture);
            }
            
            $path = $request->file('profile_picture')->store('clinic_profiles', 'public');
            $clinic->profile_picture = $path;
        }

        $clinic->save();

        return redirect()->route('clinic.settings.index')->with('success', 'Profile updated successfully.');
    }

    /**
     * Update the user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        // Check if current password is correct
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The provided password does not match your current password.']);
        }

        // Update password
        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->route('clinic.settings.index')->with('success', 'Password updated successfully.');
    }
}
