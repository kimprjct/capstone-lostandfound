<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\FoundItem;
use App\Models\LostItem;
use App\Models\Claim;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;
use App\Notifications\LostItemMatchedNotification;
use Barryvdh\DomPDF\Facade\Pdf;

class FoundItemController extends Controller
{
    public function index(Request $request)
{
    $user = auth()->user();
    if (!$user || !$user->organization_id) {
        return redirect()->route('login')
            ->with('error', 'You must be associated with an organization to access this feature.');
    }

    $organizationId = $user->organization_id;

    $query = FoundItem::with('user')
        ->where('organization_id', $organizationId)
        ->orderBy('created_at', 'desc');

    // Multi-keyword search
    if ($request->filled('search')) {
        $keywords = explode(' ', $request->search);
        $query->where(function($q) use ($keywords) {
            foreach ($keywords as $keyword) {
                $q->orWhere('title', 'like', "%$keyword%")
                  ->orWhere('category', 'like', "%$keyword%")
                  ->orWhere('location', 'like', "%$keyword%");
            }
        });
    }

    $foundItems = $query->paginate(10)->appends($request->all());

    return view('tenant.found-items.index', compact('foundItems'));
}



    public function create(): View
    {
        return view('tenant.found-items.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string|max:100',
            'location' => 'required|string|max:255',
            'date_found' => 'required|date|before_or_equal:today',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $foundItem = new FoundItem();
        $foundItem->title = $request->title;
        $foundItem->description = $request->description;
        $foundItem->category = $request->category;
        $foundItem->location = $request->location;
        $foundItem->date_found = $request->date_found;
        $foundItem->status = 'found';
        $foundItem->user_id = auth()->id();
        $foundItem->organization_id = auth()->user()->organization_id;

        if ($request->hasFile('image')) {
            $foundItem->image = $request->file('image')->store('found-items', 'public');
        }

        $foundItem->save();

        // 🔹 Match detection: check lost items in the same organization
        $organizationId = $foundItem->organization_id;
        $potentialMatches = LostItem::where('organization_id', $organizationId)
            ->where('status', 'lost')
            ->where(function ($query) use ($foundItem) {
                $query->where('title', 'like', '%' . $foundItem->title . '%')
                      ->orWhere('category', $foundItem->category);
            })
            ->get();

        foreach ($potentialMatches as $lostItem) {
            // Create a pending claim if none exists
            $existingClaim = Claim::where('found_item_id', $foundItem->id)
                ->where('lost_item_id', $lostItem->id)
                ->first();

            if (!$existingClaim) {
                Claim::create([
                    'user_id' => $lostItem->user_id,
                    'found_item_id' => $foundItem->id,
                    'lost_item_id' => $lostItem->id,
                    'organization_id' => $organizationId,
                    'claim_reason' => 'Potential match detected',
                    'status' => 'pending'
                ]);

                // Optional: send notification to lost item owner
                $lostItem->user->notify(new LostItemMatchedNotification($foundItem, $lostItem));
            }
        }

        // Send notification to organization admin about new found item
        try {
            $notificationService = app(\App\Services\NotificationService::class);
            $notificationService->notifyNewItem($foundItem);
        } catch (\Exception $e) {
            Log::error('Failed to send notification for found item: ' . $e->getMessage());
        }

        return redirect()->route('tenant.found-items.index')
            ->with('success', 'Found item has been reported successfully.');
    }

    public function show($id): View
    {
        $organization = auth()->user()->organization;
        $foundItem = FoundItem::where('id', $id)
            ->where('organization_id', $organization->id)
            ->with(['user'])
            ->firstOrFail();

        return view('tenant.found-items.show', compact('foundItem'));
    }

    public function edit($id): View
    {
        $organization = auth()->user()->organization;
        $foundItem = FoundItem::where('id', $id)
            ->where('organization_id', $organization->id)
            ->firstOrFail();

        return view('tenant.found-items.edit', compact('foundItem'));
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $organization = auth()->user()->organization;
        $foundItem = FoundItem::where('id', $id)
            ->where('organization_id', $organization->id)
            ->firstOrFail();

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string|max:100',
            'location' => 'required|string|max:255',
            'date_found' => 'required|date|before_or_equal:today',
            'status' => 'required|in:found,claimed,archived',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $foundItem->title = $request->title;
        $foundItem->description = $request->description;
        $foundItem->category = $request->category;
        $foundItem->location = $request->location;
        $foundItem->date_found = $request->date_found;
        $foundItem->status = $request->status;

        if ($request->hasFile('image')) {
            if ($foundItem->image) {
                Storage::disk('public')->delete($foundItem->image);
            }
            $foundItem->image = $request->file('image')->store('found-items', 'public');
        }

        if ($request->has('remove_image') && $foundItem->image) {
            Storage::disk('public')->delete($foundItem->image);
            $foundItem->image = null;
        }

        $foundItem->save();

        return redirect()->route('tenant.found-items.show', $foundItem->id)
            ->with('success', 'Found item has been updated successfully.');
    }

    public function destroy($id): RedirectResponse
    {
        $organization = auth()->user()->organization;
        $foundItem = FoundItem::where('id', $id)
            ->where('organization_id', $organization->id)
            ->firstOrFail();

        if ($foundItem->image) {
            Storage::disk('public')->delete($foundItem->image);
        }

        $foundItem->delete();

        return redirect()->route('tenant.found-items.index')
            ->with('success', 'Found item has been deleted successfully.');
    }

    /**
     * Manage found item - show details, auto-match, and claims
     */
    public function manage($id)
    {
        $user = auth()->user();
        if (!$user || !$user->organization_id) {
            return redirect()->route('login')
                ->with('error', 'You must be associated with an organization to access this feature.');
        }

        $foundItem = FoundItem::with(['user', 'claims.user'])
            ->where('id', $id)
            ->where('organization_id', $user->organization_id)
            ->firstOrFail();

        // Get all claims for this found item
        $claims = $foundItem->claims()->with('user')->orderBy('created_at', 'desc')->get();

        // Get potential matches (lost items with similar characteristics)
        $potentialMatches = \App\Models\LostItem::with('user')
            ->where('organization_id', $user->organization_id)
            ->where('status', \App\Models\LostItem::STATUS_UNRESOLVED)
            ->where(function($query) use ($foundItem) {
                $query->where('category', $foundItem->category)
                      ->orWhere('title', 'like', '%' . $foundItem->title . '%')
                      ->orWhere('location', 'like', '%' . $foundItem->location . '%');
            })
            ->limit(5)
            ->get();

        return view('tenant.found-items.manage', compact('foundItem', 'claims', 'potentialMatches'));
    }

    /**
     * Run auto-match for found item
     */
    public function autoMatch($id)
    {
        $user = auth()->user();
        if (!$user || !$user->organization_id) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $foundItem = FoundItem::where('id', $id)
            ->where('organization_id', $user->organization_id)
            ->firstOrFail();

        // Get potential matches with scoring
        $lostItems = \App\Models\LostItem::with('user')
            ->where('organization_id', $user->organization_id)
            ->where('status', \App\Models\LostItem::STATUS_UNRESOLVED)
            ->get();

        $matches = [];
        foreach ($lostItems as $lostItem) {
            $score = $this->calculateMatchScore($foundItem, $lostItem);
            if ($score > 0) {
                $matches[] = [
                    'item' => $lostItem,
                    'score' => $score,
                    'analysis' => $this->generateMatchAnalysis($foundItem, $lostItem, $score)
                ];
            }
        }

        // Sort by score descending
        usort($matches, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return response()->json([
            'success' => true,
            'matches' => array_slice($matches, 0, 10) // Return top 10 matches
        ]);
    }

    /**
     * Calculate match score between found and lost items
     */
    private function calculateMatchScore($foundItem, $lostItem)
    {
        $score = 0;
        
        // Category match (40 points)
        if ($foundItem->category && $lostItem->category && 
            strtolower($foundItem->category) === strtolower($lostItem->category)) {
            $score += 40;
        }
        
        // Title similarity (30 points)
        if ($foundItem->title && $lostItem->title) {
            $titleSimilarity = $this->calculateStringSimilarity(
                strtolower($foundItem->title), 
                strtolower($lostItem->title)
            );
            $score += $titleSimilarity * 30;
        }
        
        // Location similarity (20 points)
        if ($foundItem->location && $lostItem->location) {
            $locationSimilarity = $this->calculateStringSimilarity(
                strtolower($foundItem->location), 
                strtolower($lostItem->location)
            );
            $score += $locationSimilarity * 20;
        }
        
        // Date proximity (10 points)
        if ($foundItem->date_found && $lostItem->date_lost) {
            $daysDiff = abs($foundItem->date_found->diffInDays($lostItem->date_lost));
            if ($daysDiff <= 7) {
                $score += 10;
            } elseif ($daysDiff <= 30) {
                $score += 5;
            }
        }
        
        return round($score);
    }

    /**
     * Calculate string similarity using Levenshtein distance
     */
    private function calculateStringSimilarity($str1, $str2)
    {
        $maxLen = max(strlen($str1), strlen($str2));
        if ($maxLen === 0) return 1;
        
        $distance = levenshtein($str1, $str2);
        return 1 - ($distance / $maxLen);
    }

    /**
     * Generate match analysis
     */
    private function generateMatchAnalysis($foundItem, $lostItem, $score)
    {
        $analysis = [];
        
        if ($foundItem->category && $lostItem->category && 
            strtolower($foundItem->category) === strtolower($lostItem->category)) {
            $analysis[] = "Category match: Both items are {$foundItem->category}";
        }
        
        if ($foundItem->title && $lostItem->title) {
            $titleSimilarity = $this->calculateStringSimilarity(
                strtolower($foundItem->title), 
                strtolower($lostItem->title)
            );
            if ($titleSimilarity > 0.7) {
                $analysis[] = "Title similarity: High match in item names";
            } elseif ($titleSimilarity > 0.4) {
                $analysis[] = "Title similarity: Partial match in item names";
            }
        }
        
        if ($foundItem->location && $lostItem->location) {
            $locationSimilarity = $this->calculateStringSimilarity(
                strtolower($foundItem->location), 
                strtolower($lostItem->location)
            );
            if ($locationSimilarity > 0.7) {
                $analysis[] = "Location similarity: Found near where item was lost";
            }
        }
        
        if ($foundItem->date_found && $lostItem->date_lost) {
            $daysDiff = abs($foundItem->date_found->diffInDays($lostItem->date_lost));
            if ($daysDiff <= 7) {
                $analysis[] = "Timing: Found within a week of being lost";
            } elseif ($daysDiff <= 30) {
                $analysis[] = "Timing: Found within a month of being lost";
            }
        }
        
        return $analysis;
    }

    /**
     * Print unclaimed found items for bulletin board
     */
    public function print()
    {
        // Increase execution time limit for PDF generation
        set_time_limit(120);
        
        $user = auth()->user();
        if (!$user || !$user->organization_id) {
            return redirect()->route('login')
                ->with('error', 'You must be associated with an organization to access this feature.');
        }

        try {
            $organization = $user->organization;
            
            // Get only unclaimed found items with limited data
            $foundItems = FoundItem::with('user')
                ->where('organization_id', $user->organization_id)
                ->where('status', FoundItem::STATUS_UNCLAIMED)
                ->orderBy('created_at', 'desc')
                ->limit(20) // Limit to 20 items to prevent timeout
                ->get();

            // Convert image URLs to base64 for faster processing
            foreach ($foundItems as $item) {
                if ($item->image) {
                    try {
                        $imagePath = storage_path('app/public/' . $item->image);
                        if (file_exists($imagePath)) {
                            $imageData = base64_encode(file_get_contents($imagePath));
                            $item->image_base64 = 'data:image/jpeg;base64,' . $imageData;
                        }
                    } catch (\Exception $e) {
                        $item->image_base64 = null;
                    }
                }
            }

            $pdf = Pdf::loadView('tenant.found-items.print-simple', compact('foundItems', 'organization'))
                ->setPaper('A4', 'portrait')
                ->setOptions([
                    'isHtml5ParserEnabled' => false, // Disable for faster processing
                    'isRemoteEnabled' => false, // Disable remote loading
                    'defaultFont' => 'Arial',
                    'isPhpEnabled' => false,
                    'isJavascriptEnabled' => false,
                    'debugKeepTemp' => false,
                    'debugCss' => false,
                    'debugLayout' => false,
                    'debugLayoutLines' => false,
                    'debugLayoutBlocks' => false,
                    'debugLayoutInline' => false,
                    'debugLayoutPaddingBox' => false
                ]);

            return $pdf->download('unclaimed-found-items-' . now()->format('Y-m-d') . '.pdf');
            
        } catch (\Exception $e) {
            Log::error('PDF Generation Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate PDF. Please try again.');
        }
    }
}
