<?php
// app/Http/Controllers/Step1Controller.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClinicInfo;
use Illuminate\Support\Facades\Auth;

class Step1Controller extends Controller
{
    public function create()
    {
        return view('admin.step1');
    }

public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'address' => 'required|string',
        'email' => 'required|email',
        'contact_number' => 'required|string|max:20',
        'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
    ]);

    $logoPath = null;
    if ($request->hasFile('logo')) {
        $logoPath = $request->file('logo')->store('logos', 'public');
    }

    // Temporarily store in session for Step 2
    session([
        'clinic_info' => [
            'clinic_name' => $request->name,
            'address' => $request->address,
            'contact_number' => $request->contact_number,
            'email' => $request->email,
            'logo' => $logoPath,
        ]
    ]);

    return redirect()->route('step2.create');
}

}
