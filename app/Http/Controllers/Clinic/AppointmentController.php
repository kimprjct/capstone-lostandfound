<?php

namespace App\Http\Controllers\Clinic;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\ClinicInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    /**
     * Display a listing of appointments.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $clinic = ClinicInfo::where('user_id', Auth::id())->firstOrFail();
        $appointments = Appointment::where('clinic_id', $clinic->id)
            ->orderByRaw("CASE 
                WHEN status = 'pending' THEN 1
                WHEN status = 'confirmed' THEN 2
                WHEN status = 'completed' THEN 3
                WHEN status = 'cancelled' THEN 4
                ELSE 5 END")
            ->orderBy('created_at', 'desc')
            ->paginate(15);
            
        return view('clinic.appointments.index', compact('appointments', 'clinic'));
    }
    
    /**
     * Show the form for viewing an appointment.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $clinic = ClinicInfo::where('user_id', Auth::id())->firstOrFail();
        $appointment = Appointment::where('clinic_id', $clinic->id)
            ->with(['customValues.field'])
            ->findOrFail($id);
            
        return view('clinic.appointments.show', compact('appointment', 'clinic'));
    }
    
    /**
     * Update the specified appointment status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateStatus(Request $request, $id)
    {
        $clinic = ClinicInfo::where('user_id', Auth::id())->firstOrFail();
        $appointment = Appointment::where('clinic_id', $clinic->id)->findOrFail($id);
        
        $request->validate([
            'status' => 'required|in:pending,confirmed,completed,cancelled',
        ]);
        
        $appointment->status = $request->status;
        $appointment->save();
        
        return redirect()->route('clinic.appointments.show', $id)
            ->with('success', 'Appointment status updated successfully');
    }
}
