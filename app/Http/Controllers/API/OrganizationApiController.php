<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Http\Request;

class OrganizationApiController extends Controller
{
    /**
     * Display a listing of organizations.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $organizations = Organization::select('id', 'name', 'address', 'logo')
            ->latest()
            ->get();

        // Transform organization data to include full URL for logo
        $organizations = $organizations->map(function ($organization) {
            $data = [
                'id' => $organization->id,
                'name' => $organization->name,
                'address' => $organization->address,
                'logo_url' => $organization->logo ? asset('storage/' . $organization->logo) : null,
            ];
            return $data;
        });
        
        return response()->json($organizations);
    }

    /**
     * Display the specified organization.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $organization = Organization::findOrFail($id);
        
        $data = [
            'id' => $organization->id,
            'name' => $organization->name,
            'address' => $organization->address,
            'logo_url' => $organization->logo ? asset('storage/' . $organization->logo) : null,
            // Add any additional fields you want to include
        ];
        
        return response()->json($data);
    }
}