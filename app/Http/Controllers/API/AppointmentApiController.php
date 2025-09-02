<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AppointmentFieldValue;
use App\Models\ClinicField;
use App\Models\ClinicInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AppointmentApiController extends Controller
{
    /**
     * Store a new appointment from the mobile app
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $clinicId
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, $clinicId)
    {
        // Validate clinic exists
        $clinic = ClinicInfo::findOrFail($clinicId);
        
        // Basic validation
        $validator = Validator::make($request->all(), [
            'owner_name' => 'required|string|max:255',
            'owner_phone' => 'required|string|max:20',
            'responses' => 'required|array',
            'responses.*.field_id' => 'required|exists:clinic_fields,id',
            'responses.*.value' => 'nullable',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Create the appointment
        $appointment = Appointment::create([
            'clinic_id' => $clinic->id,
            'owner_name' => $request->owner_name,
            'owner_phone' => $request->owner_phone,
            'status' => 'pending', // Default status
        ]);
        
        // Process field responses
        foreach ($request->responses as $response) {
            $fieldId = $response['field_id'];
            $value = $response['value'];
            
            // Store the value - we're using json casting in the model
            AppointmentFieldValue::create([
                'appointment_id' => $appointment->id,
                'clinic_field_id' => $fieldId,
                'value' => $value, // Will be cast to JSON if array
            ]);
        }
        
        return response()->json([
            'message' => 'Appointment created successfully',
            'appointment_id' => $appointment->id
        ], 201);
    }
    
    /**
     * Get all appointments for a clinic
     *
     * @param  int  $clinicId
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($clinicId)
    {
        // Ensure clinic exists
        $clinic = ClinicInfo::findOrFail($clinicId);
        
        // Get appointments with their values
        $appointments = Appointment::where('clinic_id', $clinicId)
            ->with(['customValues.field'])
            ->orderBy('created_at', 'desc')
            ->get();
            
        return response()->json([
            'data' => $appointments->map(function ($appointment) {
                return [
                    'id' => $appointment->id,
                    'owner_name' => $appointment->owner_name,
                    'owner_phone' => $appointment->owner_phone,
                    'status' => $appointment->status,
                    'created_at' => $appointment->created_at->format('Y-m-d H:i:s'),
                    'responses' => $appointment->customValues->map(function ($value) {
                        return [
                            'field_id' => $value->clinic_field_id,
                            'field_label' => $value->field->label,
                            'field_type' => $value->field->type,
                            'value' => $value->value,
                        ];
                    }),
                ];
            }),
        ]);
    }
    
    /**
     * Get a specific appointment
     *
     * @param  int  $clinicId
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($clinicId, $id)
    {
        $appointment = Appointment::where('clinic_id', $clinicId)
            ->with(['customValues.field'])
            ->findOrFail($id);
            
        return response()->json([
            'data' => [
                'id' => $appointment->id,
                'owner_name' => $appointment->owner_name,
                'owner_phone' => $appointment->owner_phone,
                'status' => $appointment->status,
                'created_at' => $appointment->created_at->format('Y-m-d H:i:s'),
                'responses' => $appointment->customValues->map(function ($value) {
                    return [
                        'field_id' => $value->clinic_field_id,
                        'field_label' => $value->field->label,
                        'field_type' => $value->field->type,
                        'value' => $value->value,
                    ];
                }),
            ],
        ]);
    }
    
    /**
     * Update appointment status
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $clinicId
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request, $clinicId, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:pending,confirmed,cancelled,completed',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $appointment = Appointment::where('clinic_id', $clinicId)
            ->findOrFail($id);
            
        $appointment->status = $request->status;
        $appointment->save();
        
        return response()->json([
            'message' => 'Appointment status updated',
            'status' => $appointment->status,
        ]);
    }
}
