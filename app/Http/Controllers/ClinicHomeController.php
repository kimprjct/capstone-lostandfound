<?php

namespace App\Http\Controllers;

use App\Models\ClinicInfo;
use App\Models\ClinicHomepage;
use App\Models\ClinicService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClinicHomeController extends Controller
{
    public function index()
    {
        $clinic = ClinicInfo::where('user_id', auth()->id())->firstOrFail();
        $homepage = ClinicHomepage::firstOrCreate(['clinic_id' => $clinic->id], []);
        $services = ClinicService::where('clinic_id', $clinic->id)->orderBy('order')->get();

        return view('clinic.HomeScreen', compact('clinic', 'homepage', 'services'));
    }

    public function updateContent(Request $request)
    {
        $clinic = ClinicInfo::where('user_id', auth()->id())->firstOrFail();
        $homepage = ClinicHomepage::firstOrCreate(['clinic_id' => $clinic->id], []);

        $validated = $request->validate([
            'hero_title' => 'required|string|max:255',
            'hero_subtitle' => 'nullable|string|max:255',
            'about_text' => 'nullable|string',
            'announcement_title' => 'nullable|string|max:255',
            'announcement_body' => 'nullable|string',
            'hero_image' => 'nullable|image|max:4096',
            'announcement_image' => 'nullable|image|max:4096',
        ]);

        if ($request->hasFile('hero_image')) {
            if ($homepage->hero_image) Storage::disk('public')->delete($homepage->hero_image);
            $validated['hero_image'] = $request->file('hero_image')->store('clinic/home/hero', 'public');
        }

        if ($request->hasFile('announcement_image')) {
            if ($homepage->announcement_image) Storage::disk('public')->delete($homepage->announcement_image);
            $validated['announcement_image'] = $request->file('announcement_image')->store('clinic/home/ann', 'public');
        }

        $homepage->update($validated);

        return back()->with('success', 'Homepage updated.');
    }

    public function storeService(Request $request)
    {
        $clinic = ClinicInfo::where('user_id', auth()->id())->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'icon' => 'nullable|string|max:64',
            'image' => 'nullable|image|max:4096',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $data = [
            'clinic_id' => $clinic->id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'] ?? null,
            'icon' => $validated['icon'] ?? null,
            'order' => $validated['order'] ?? 0,
            'is_active' => (bool)($validated['is_active'] ?? true),
        ];

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('clinic/services', 'public');
        }

        ClinicService::create($data);

        return back()->with('success', 'Service added.');
    }

    public function updateService(Request $request, ClinicService $service)
    {
        $clinic = ClinicInfo::where('user_id', auth()->id())->firstOrFail();
        abort_if($service->clinic_id !== $clinic->id, 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'icon' => 'nullable|string|max:64',
            'image' => 'nullable|image|max:4096',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        if ($request->hasFile('image')) {
            if ($service->image_path) Storage::disk('public')->delete($service->image_path);
            $validated['image_path'] = $request->file('image')->store('clinic/services', 'public');
        }

        $service->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'] ?? null,
            'icon' => $validated['icon'] ?? null,
            'image_path' => $validated['image_path'] ?? $service->image_path,
            'order' => $validated['order'] ?? $service->order,
            'is_active' => (bool)($validated['is_active'] ?? $service->is_active),
        ]);

        return back()->with('success', 'Service updated.');
    }

    public function destroyService(ClinicService $service)
    {
        $clinic = ClinicInfo::where('user_id', auth()->id())->firstOrFail();
        abort_if($service->clinic_id !== $clinic->id, 403);

        if ($service->image_path) Storage::disk('public')->delete($service->image_path);
        $service->delete();

        return back()->with('success', 'Service deleted.');
    }
}