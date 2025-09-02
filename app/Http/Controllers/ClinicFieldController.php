<?php

namespace App\Http\Controllers;

use App\Models\ClinicField;
use App\Models\ClinicInfo;
use Illuminate\Http\Request;

class ClinicFieldController extends Controller
{
    // Show list + create form
    public function index()
    {
        $clinic = ClinicInfo::where('user_id', auth()->id())->firstOrFail();
        $fields = ClinicField::where('clinic_id', $clinic->id)->orderBy('order')->get();

        return view('clinic.manage_fields', compact('clinic', 'fields'));
    }

    // Store new field
    public function store(Request $request)
    {
        $clinic = ClinicInfo::where('user_id', auth()->id())->firstOrFail();

        $request->validate([
            'label' => 'required|string|max:255',
            'type' => 'required|in:text,textarea,select,checkbox,radio,date,time,number',
            'options' => 'nullable|string', // comma separated for select/checkbox/radio
            'required' => 'sometimes|boolean',
        ]);

        $options = null;
        if (in_array($request->type, ['select','checkbox','radio']) && $request->filled('options')) {
            // Turn comma-separated into array, trim items, remove empties
            $options = array_values(array_filter(array_map('trim', explode(',', $request->options))));
        }

        $order = ClinicField::where('clinic_id', $clinic->id)->count() + 1;

        ClinicField::create([
            'clinic_id' => $clinic->id,
            'label' => $request->label,
            'type' => $request->type,
            'options' => $options,
            'required' => $request->boolean('required'),
            'order' => $order,
        ]);

        return redirect()->route('clinic.fields.index')->with('success', 'Field added successfully.');
    }

    // Edit page
    public function edit($id)
    {
        $clinic = ClinicInfo::where('user_id', auth()->id())->firstOrFail();
        $field = ClinicField::where('clinic_id', $clinic->id)->findOrFail($id);

        return view('clinic.edit_field', compact('clinic','field'));
    }

    // Update
    public function update(Request $request, $id)
    {
        $clinic = ClinicInfo::where('user_id', auth()->id())->firstOrFail();
        $field = ClinicField::where('clinic_id', $clinic->id)->findOrFail($id);

        $request->validate([
            'label' => 'required|string|max:255',
            'type' => 'required|in:text,textarea,select,checkbox,radio,date,time,number',
            'options' => 'nullable|string',
            'required' => 'sometimes|boolean',
        ]);

        $options = null;
        if (in_array($request->type, ['select','checkbox','radio']) && $request->filled('options')) {
            $options = array_values(array_filter(array_map('trim', explode(',', $request->options))));
        }

        $field->update([
            'label' => $request->label,
            'type' => $request->type,
            'options' => $options,
            'required' => $request->boolean('required'),
        ]);

        return redirect()->route('clinic.fields.index')->with('success', 'Field updated.');
    }

    // Delete
    public function destroy($id)
    {
        $clinic = ClinicInfo::where('user_id', auth()->id())->firstOrFail();
        $field = ClinicField::where('clinic_id', $clinic->id)->findOrFail($id);
        $field->delete();

        return redirect()->route('clinic.fields.index')->with('success', 'Field deleted.');
    }
}
