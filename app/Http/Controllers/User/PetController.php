<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Pet;
use App\Models\PetVaccination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class PetController extends Controller
{
    /**
     * Display a listing of the user's pets.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $pets = Auth::user()->pets()->with('vaccinations')->get();
        return view('user.mypet', compact('pets'));
    }

    /**
     * Store a newly created pet in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'breed' => 'required|string|max:255',
            'age' => 'required|numeric|min:0',
            'birthday' => 'required|date',
            'last_vaccination_date' => 'nullable|date',
            'vaccination_details' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'notes' => 'nullable|string',
        ]);

        $data = $request->except('image');
        $data['user_id'] = Auth::id();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('pet-images', 'public');
        }

        Pet::create($data);

        return redirect()->route('user.pets.index')->with('success', 'Pet added successfully!');
    }

    /**
     * Update the specified pet in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Pet  $pet
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Pet $pet)
    {
        // Ensure the user owns this pet
        if ($pet->user_id !== Auth::id()) {
            return redirect()->route('user.pets.index')->with('error', 'Unauthorized access.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'breed' => 'required|string|max:255',
            'age' => 'required|numeric|min:0',
            'birthday' => 'required|date',
            'last_vaccination_date' => 'nullable|date',
            'vaccination_details' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'notes' => 'nullable|string',
        ]);

        $data = $request->except('image');

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($pet->image && Storage::disk('public')->exists($pet->image)) {
                Storage::disk('public')->delete($pet->image);
            }
            $data['image'] = $request->file('image')->store('pet-images', 'public');
        }

        $pet->update($data);

        return redirect()->route('user.pets.index')->with('success', 'Pet updated successfully!');
    }

    /**
     * Remove the specified pet from storage.
     *
     * @param  \App\Models\Pet  $pet
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Pet $pet)
    {
        // Ensure the user owns this pet
        if ($pet->user_id !== Auth::id()) {
            return redirect()->route('user.pets.index')->with('error', 'Unauthorized access.');
        }

        // Delete pet image if exists
        if ($pet->image && Storage::disk('public')->exists($pet->image)) {
            Storage::disk('public')->delete($pet->image);
        }

        $pet->delete();

        return redirect()->route('user.pets.index')->with('success', 'Pet deleted successfully!');
    }

    /**
     * Store a new vaccination record for a pet.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Pet  $pet
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeVaccination(Request $request, Pet $pet)
    {
        // Ensure the user owns this pet
        if ($pet->user_id !== Auth::id()) {
            return redirect()->route('user.pets.index')->with('error', 'Unauthorized access.');
        }

        $request->validate([
            'vaccine_name' => 'required|string|max:255',
            'vaccination_date' => 'required|date',
            'next_due_date' => 'nullable|date',
            'administered_by' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $vaccination = new PetVaccination($request->all());
        $pet->vaccinations()->save($vaccination);

        // Update the pet's last vaccination date if this is the most recent
        if ($pet->last_vaccination_date === null || 
            $request->vaccination_date > $pet->last_vaccination_date) {
            $pet->update(['last_vaccination_date' => $request->vaccination_date]);
        }

        return redirect()->route('user.pets.index')->with('success', 'Vaccination record added successfully!');
    }
}
