<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    public function index()
    {
        $staff = Staff::orderBy('forename')
            ->orderBy('surname')
            ->get();

        return view('staff.index', compact('staff'));
    }

    public function create()
    {
        return view('staff.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'forename' => ['required', 'string', 'max:100'],
            'surname' => ['required', 'string', 'max:100'],
            'regul8_staff_id' => [
                'nullable',
                'integer',
                'unique:staff,regul8_staff_id',
            ],
            'active' => ['nullable', 'boolean'],
        ]);

        Staff::create([
            'forename' => $validated['forename'],
            'surname' => $validated['surname'],
            'regul8_staff_id' => $validated['regul8_staff_id'] ?? null,
            'active' => $request->boolean('active'),
        ]);

        return redirect()
            ->route('staff.index')
            ->with('success', 'Staff member created successfully.');
    }

    public function show(Staff $staff)
    {
        return view('staff.show', compact('staff'));
    }

    public function edit(Staff $staff)
    {
        return view('staff.edit', compact('staff'));
    }

    public function update(Request $request, Staff $staff)
    {
        $validated = $request->validate([
            'forename' => ['required', 'string', 'max:100'],
            'surname' => ['required', 'string', 'max:100'],
            'regul8_staff_id' => [
                'nullable',
                'integer',
                Rule::unique('staff', 'regul8_staff_id')->ignore($staff->id),
            ],
            'active' => ['nullable', 'boolean'],
        ]);

        $staff->update([
            'forename' => $validated['forename'],
            'surname' => $validated['surname'],
            'regul8_staff_id' => $validated['regul8_staff_id'] ?? null,
            'active' => $request->boolean('active'),
        ]);

        return redirect()
            ->route('staff.show', $staff)
            ->with('success', 'Staff member updated successfully.');
    }
}