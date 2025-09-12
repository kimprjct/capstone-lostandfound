<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\FoundItem;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class FoundItemApiController extends Controller
{
    /**
     * Get all found items for the authenticated user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();
        $foundItems = FoundItem::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $foundItems
        ]);
    }

    /**
     * Get all found items for a specific organization
     * This endpoint is publicly accessible without authentication
     *
     * @param  int  $organizationId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByOrganization($organizationId): JsonResponse
    {
        // Verify the organization exists
        $organization = Organization::find($organizationId);
        
        if (!$organization) {
            return response()->json([
                'success' => false,
                'message' => 'Organization not found'
            ], 404);
        }
        
        // Get all found items for this organization
        $foundItems = FoundItem::where('organization_id', $organizationId)
            ->where('status', 'found') // Only show active found items
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $foundItems
        ]);
    }

    /**
     * Store a newly created found item in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'organization_id' => 'required|exists:organizations,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'required|string|max:255',
            'date_found' => 'required|date',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'category' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        
        $foundItem = new FoundItem();
        $foundItem->user_id = $user->id;
        $foundItem->organization_id = $request->organization_id;
        $foundItem->title = $request->title;
        $foundItem->description = $request->description;
        $foundItem->category = $request->category;
        $foundItem->location = $request->location;
        $foundItem->date_found = $request->date_found;
        $foundItem->status = 'found';

        // Handle file upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            
            // Store the file in the public storage
            $path = $image->storeAs('found_items', $imageName, 'public');
            $foundItem->image = $path;
        }

        $foundItem->save();

        return response()->json([
            'success' => true,
            'message' => 'Found item reported successfully',
            'data' => $foundItem
        ], 201);
    }

    /**
     * Display the specified found item.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id): JsonResponse
    {
        $user = Auth::user();
        $foundItem = FoundItem::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$foundItem) {
            return response()->json([
                'success' => false,
                'message' => 'Found item not found or not authorized'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $foundItem
        ]);
    }

    /**
     * Update the specified found item in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'location' => 'sometimes|required|string|max:255',
            'date_found' => 'sometimes|required|date',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'category' => 'sometimes|required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $foundItem = FoundItem::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$foundItem) {
            return response()->json([
                'success' => false,
                'message' => 'Found item not found or not authorized'
            ], 404);
        }

        if ($request->has('title')) {
            $foundItem->title = $request->title;
        }

        if ($request->has('description')) {
            $foundItem->description = $request->description;
        }

        if ($request->has('location')) {
            $foundItem->location = $request->location;
        }

        if ($request->has('date_found')) {
            $foundItem->date_found = $request->date_found;
        }

        if ($request->has('category')) {
            $foundItem->category = $request->category;
        }

        // Handle file upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($foundItem->image) {
                Storage::disk('public')->delete($foundItem->image);
            }
            
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            
            // Store the file
            $path = $image->storeAs('found_items', $imageName, 'public');
            $foundItem->image = $path;
        }

        $foundItem->save();

        return response()->json([
            'success' => true,
            'message' => 'Found item updated successfully',
            'data' => $foundItem
        ]);
    }

    /**
     * Remove the specified found item from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $user = Auth::user();
        $foundItem = FoundItem::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$foundItem) {
            return response()->json([
                'success' => false,
                'message' => 'Found item not found or not authorized'
            ], 404);
        }

        // Delete image if exists
        if ($foundItem->image) {
            Storage::disk('public')->delete($foundItem->image);
        }

        $foundItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'Found item deleted successfully'
        ]);
    }
    
    /**
     * Get organizations for dropdown
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrganizations(): JsonResponse
    {
        $organizations = Organization::select('id', 'name')->get();
        
        return response()->json([
            'success' => true,
            'data' => $organizations
        ]);
    }
}
