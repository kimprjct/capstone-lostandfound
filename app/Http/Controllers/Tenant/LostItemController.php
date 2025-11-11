<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\LostItem;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class LostItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
{
    $user = auth()->user();
    if (!$user || !$user->organization_id) {
        return redirect()->route('login')
            ->with('error', 'You must be associated with an organization to access this feature.');
    }

    $organizationId = $user->organization_id;

    $query = LostItem::with('user')
        ->where('organization_id', $organizationId)
        ->orderBy('created_at', 'desc');

    // Multiple keywords search
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

    $lostItems = $query->paginate(10)->appends($request->all());

    return view('tenant.lost-items.index', compact('lostItems'));
}



    public function create(): View
    {
        return view('tenant.lost-items.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string|max:100',
            'location' => 'required|string|max:255',
            'date_lost' => 'required|date|before_or_equal:today',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $lostItem = new LostItem();
        $lostItem->title = $request->title;
        $lostItem->description = $request->description;
        $lostItem->category = $request->category;
        $lostItem->location = $request->location;
        $lostItem->date_lost = $request->date_lost;
        $lostItem->status = 'lost';
        $lostItem->user_id = auth()->id();
        $lostItem->organization_id = auth()->user()->organization_id;

        if ($request->hasFile('image')) {
            $lostItem->image = $request->file('image')->store('lost-items', 'public');
        }

        $lostItem->save();

        // Send notification to organization admin about new lost item
        try {
            $notificationService = app(\App\Services\NotificationService::class);
            $notificationService->notifyNewItem($lostItem);
        } catch (\Exception $e) {
            Log::error('Failed to send notification for lost item: ' . $e->getMessage());
        }

        return redirect()->route('tenant.lost-items.index')
            ->with('success', 'Lost item has been reported successfully.');
    }

    public function show($id): View
    {
        $organization = auth()->user()->organization;
        $lostItem = LostItem::where('id', $id)
            ->where('organization_id', $organization->id)
            ->with(['user'])
            ->firstOrFail();

        return view('tenant.lost-items.show', compact('lostItem'));
    }

    public function edit($id): View
    {
        $organization = auth()->user()->organization;
        $lostItem = LostItem::where('id', $id)
            ->where('organization_id', $organization->id)
            ->firstOrFail();

        return view('tenant.lost-items.edit', compact('lostItem'));
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $organization = auth()->user()->organization;
        $lostItem = LostItem::where('id', $id)
            ->where('organization_id', $organization->id)
            ->firstOrFail();

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string|max:100',
            'location' => 'required|string|max:255',
            'date_lost' => 'required|date|before_or_equal:today',
            'status' => 'required|in:lost,claimed,archived',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $lostItem->title = $request->title;
        $lostItem->description = $request->description;
        $lostItem->category = $request->category;
        $lostItem->location = $request->location;
        $lostItem->date_lost = $request->date_lost;
        $lostItem->status = $request->status;

        if ($request->hasFile('image')) {
            if ($lostItem->image) {
                Storage::disk('public')->delete($lostItem->image);
            }
            $lostItem->image = $request->file('image')->store('lost-items', 'public');
        }

        if ($request->has('remove_image') && $lostItem->image) {
            Storage::disk('public')->delete($lostItem->image);
            $lostItem->image = null;
        }

        $lostItem->save();

        return redirect()->route('tenant.lost-items.show', $lostItem->id)
            ->with('success', 'Lost item has been updated successfully.');
    }

    public function destroy($id): RedirectResponse
    {
        $organization = auth()->user()->organization;
        $lostItem = LostItem::where('id', $id)
            ->where('organization_id', $organization->id)
            ->firstOrFail();

        if ($lostItem->image) {
            Storage::disk('public')->delete($lostItem->image);
        }

        $lostItem->delete();

        return redirect()->route('tenant.lost-items.index')
            ->with('success', 'Lost item has been deleted successfully.');
    }

    /**
     * Manage lost item - show details, auto-match, and claims
     */
    public function manage($id)
    {
        $user = auth()->user();
        if (!$user || !$user->organization_id) {
            return redirect()->route('login')
                ->with('error', 'You must be associated with an organization to access this feature.');
        }

        $lostItem = LostItem::with(['user', 'claims.user'])
            ->where('id', $id)
            ->where('organization_id', $user->organization_id)
            ->firstOrFail();

        // Get all claims for this lost item
        $claims = $lostItem->claims()->with('user')->orderBy('created_at', 'desc')->get();

        // Get potential matches (found items with similar characteristics)
        $potentialMatches = \App\Models\FoundItem::with('user')
            ->where('organization_id', $user->organization_id)
            ->where('status', \App\Models\FoundItem::STATUS_UNCLAIMED)
            ->where(function($query) use ($lostItem) {
                $query->where('category', $lostItem->category)
                      ->orWhere('title', 'like', '%' . $lostItem->title . '%')
                      ->orWhere('location', 'like', '%' . $lostItem->location . '%');
            })
            ->limit(5)
            ->get();

        return view('tenant.lost-items.manage', compact('lostItem', 'claims', 'potentialMatches'));
    }

    /**
     * Run auto-match for lost item
     */
    public function autoMatch($id)
    {
        $user = auth()->user();
        if (!$user || !$user->organization_id) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $lostItem = LostItem::where('id', $id)
            ->where('organization_id', $user->organization_id)
            ->firstOrFail();

        // Get potential matches with scoring
        $foundItems = \App\Models\FoundItem::with('user')
            ->where('organization_id', $user->organization_id)
            ->where('status', \App\Models\FoundItem::STATUS_UNCLAIMED)
            ->get();

        $matches = [];
        foreach ($foundItems as $foundItem) {
            $score = $this->calculateMatchScore($lostItem, $foundItem);
            if ($score > 0) {
                $matches[] = [
                    'item' => $foundItem,
                    'score' => $score,
                    'analysis' => $this->generateMatchAnalysis($lostItem, $foundItem, $score)
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
     * Calculate match score between lost and found items
     */
    private function calculateMatchScore($lostItem, $foundItem)
    {
        $score = 0;
        
        // Category match (40 points)
        if ($lostItem->category && $foundItem->category && 
            strtolower($lostItem->category) === strtolower($foundItem->category)) {
            $score += 40;
        }
        
        // Title similarity (30 points)
        if ($lostItem->title && $foundItem->title) {
            $titleSimilarity = $this->calculateStringSimilarity(
                strtolower($lostItem->title), 
                strtolower($foundItem->title)
            );
            $score += $titleSimilarity * 30;
        }
        
        // Location similarity (20 points)
        if ($lostItem->location && $foundItem->location) {
            $locationSimilarity = $this->calculateStringSimilarity(
                strtolower($lostItem->location), 
                strtolower($foundItem->location)
            );
            $score += $locationSimilarity * 20;
        }
        
        // Date proximity (10 points)
        if ($lostItem->date_lost && $foundItem->date_found) {
            $daysDiff = abs($lostItem->date_lost->diffInDays($foundItem->date_found));
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
    private function generateMatchAnalysis($lostItem, $foundItem, $score)
    {
        $analysis = [];
        
        if ($lostItem->category && $foundItem->category && 
            strtolower($lostItem->category) === strtolower($foundItem->category)) {
            $analysis[] = "Category match: Both items are {$lostItem->category}";
        }
        
        if ($lostItem->title && $foundItem->title) {
            $titleSimilarity = $this->calculateStringSimilarity(
                strtolower($lostItem->title), 
                strtolower($foundItem->title)
            );
            if ($titleSimilarity > 0.7) {
                $analysis[] = "Title similarity: High match in item names";
            } elseif ($titleSimilarity > 0.4) {
                $analysis[] = "Title similarity: Partial match in item names";
            }
        }
        
        if ($lostItem->location && $foundItem->location) {
            $locationSimilarity = $this->calculateStringSimilarity(
                strtolower($lostItem->location), 
                strtolower($foundItem->location)
            );
            if ($locationSimilarity > 0.7) {
                $analysis[] = "Location similarity: Found near where item was lost";
            }
        }
        
        if ($lostItem->date_lost && $foundItem->date_found) {
            $daysDiff = abs($lostItem->date_lost->diffInDays($foundItem->date_found));
            if ($daysDiff <= 7) {
                $analysis[] = "Timing: Found within a week of being lost";
            } elseif ($daysDiff <= 30) {
                $analysis[] = "Timing: Found within a month of being lost";
            }
        }
        
        return $analysis;
    }

    /**
     * Print unresolved lost items for bulletin board
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
            
            // Get only unresolved lost items with limited data
            $lostItems = LostItem::with('user')
                ->where('organization_id', $user->organization_id)
                ->where('status', LostItem::STATUS_UNRESOLVED)
                ->orderBy('created_at', 'desc')
                ->limit(20) // Limit to 20 items to prevent timeout
                ->get();

            // Convert image URLs to base64 for faster processing
            foreach ($lostItems as $item) {
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

            $pdf = Pdf::loadView('tenant.lost-items.print-simple', compact('lostItems', 'organization'))
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

            return $pdf->download('unresolved-lost-items-' . now()->format('Y-m-d') . '.pdf');
            
        } catch (\Exception $e) {
            Log::error('PDF Generation Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate PDF. Please try again.');
        }
    }
}
