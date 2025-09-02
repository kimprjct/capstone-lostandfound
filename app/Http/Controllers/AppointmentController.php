<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AppointmentFieldValue;
use App\Models\ClinicField;
use App\Models\ClinicInfo;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function store(Request $request, $clinicId)
    {
        $clinic = ClinicInfo::findOrFail($clinicId);
        $customFields = ClinicField::where('clinic_id', $clinic->id)->get();

        // Validate request
        $request->validate([
            'owner_name' => 'required|string|max:255',
            'owner_phone' => 'required|string|max:20',
        ]);

        // Create appointment
        $appointment = Appointment::create([
            'clinic_id' => $clinic->id,
            'owner_name' => $request->owner_name,
            'owner_phone' => $request->owner_phone,
            'status' => 'pending',
        ]);

        // Save custom field values
        foreach ($customFields as $field) {
            $fieldId = $field->id;
            $value = $request->input('field_' . $fieldId);
            
            // Handle checkbox arrays
            if (is_array($value)) {
                $value = array_values($value);
            }
            
            AppointmentFieldValue::create([
                'appointment_id' => $appointment->id,
                'clinic_field_id' => $fieldId,
                'value' => $value,
            ]);
        }

        return redirect()->route('appointments.show', $appointment->id)
                         ->with('success', 'Appointment booked successfully!');
    }

    public function show($id)
    {
        $appointment = Appointment::with(['customValues.field', 'clinic'])->findOrFail($id);
        return view('appointments.show', compact('appointment'));
    }

    public function previewForm($clinicId)
{
    $clinic = ClinicInfo::findOrFail($clinicId);
    $customFields = ClinicField::where('clinic_id', $clinicId)
                               ->orderBy('order')
                               ->get();

    return view('clinic.appointments.preview', compact('clinic', 'customFields'));
}
}
