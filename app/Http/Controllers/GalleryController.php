<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class GalleryController extends Controller
{
 public function index()
{
    $clinic = Auth::user()->clinicInfo;

    if (!$clinic) {
        return redirect()->back()->withErrors(['message' => 'No clinic info found for this user.']);
    }

    $images = Gallery::where('clinic_id', $clinic->id)->get();
    return view('clinic.gallery', compact('images', 'clinic'));
}


public function store(Request $request)
{
    $request->validate([
        'image' => 'required|image|max:2048'
    ]);

    $clinic = Auth::user()->clinicInfo;

    if (!$clinic) {
        return redirect()->back()->withErrors(['message' => 'No clinic info found.']);
    }

    $path = $request->file('image')->store('gallery', 'public');

    Gallery::create([
        'clinic_id' => $clinic->id,
        'image_path' => $path,
    ]);

    return redirect()->back()->with('success', 'Image uploaded!');
}


   public function destroy($id)
{
    $image = Gallery::findOrFail($id);
    $clinic = Auth::user()->clinicInfo;

    if (!$clinic || $image->clinic_id != $clinic->id) {
        abort(403);
    }

    Storage::disk('public')->delete($image->image_path);
    $image->delete();

    return redirect()->back()->with('success', 'Image deleted!');
}

}

