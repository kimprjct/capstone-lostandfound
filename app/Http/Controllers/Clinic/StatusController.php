<?php

namespace App\Http\Controllers\Clinic;

use App\Http\Controllers\Controller;
use App\Models\ClinicInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StatusController extends Controller
{
    /**
     * Update the clinic open/closed status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateStatus(Request $request)
    {
        $user = Auth::user();
        $clinic = ClinicInfo::where('user_id', $user->id)->first();
        
        if ($clinic) {
            $isOpen = $request->has('is_open') ? true : false;
            $clinic->is_open = $isOpen;
            $clinic->save();
            
            return back()->with('success', 'Clinic status updated successfully');
        }
        
        return back()->with('error', 'Clinic not found');
    }
}
