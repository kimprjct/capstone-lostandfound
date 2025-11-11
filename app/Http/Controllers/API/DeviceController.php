<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\UserDevice;

class DeviceController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'expo_push_token' => 'required|string',
            'platform' => 'nullable|string|in:ios,android,web,expo',
            'device_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $device = UserDevice::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'expo_push_token' => $request->input('expo_push_token'),
            ],
            [
                'platform' => $request->input('platform'),
                'device_name' => $request->input('device_name'),
            ]
        );

        return response()->json([
            'success' => true,
            'data' => $device,
        ]);
    }
}


