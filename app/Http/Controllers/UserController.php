<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['staff', 'role'])
            ->join('staff', 'users.staff_id', '=', 'staff.id')
            ->orderBy('staff.forename')
            ->orderBy('staff.surname')
            ->select('users.*')
            ->get();

        return view('users.index', compact('users'));
    }

    public function create()
    {
        $staff = Staff::whereDoesntHave('user')
            ->orderBy('forename')
            ->orderBy('surname')
            ->get();

        $roles = Role::orderBy('role_name')->get();

        return view('users.create', compact('staff', 'roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'staff_id' => [
                'required',
                'integer',
                'exists:staff,id',
                'unique:users,staff_id',
            ],
            'role_id' => [
                'required',
                'integer',
                'exists:roles,id',
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],
            'password' => [
                'required',
                'confirmed',
                Password::defaults(),
            ],
        ]);

        User::create([
            'staff_id' => $validated['staff_id'],
            'role_id' => $validated['role_id'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'active' => true,
        ]);

        return redirect()
            ->route('users.index')
            ->with('success', 'Application user created successfully.');
    }

    public function edit(User $user)
    {
        $user->load(['staff', 'role']);

        $roles = Role::orderBy('role_name')->get();

        return view('users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'role_id' => [
                'required',
                'integer',
                'exists:roles,id',
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'active' => [
                'nullable',
                'boolean',
            ],
            'password' => [
                'nullable',
                'confirmed',
                Password::defaults(),
            ],
        ]);

        $updateData = [
            'role_id' => $validated['role_id'],
            'email' => $validated['email'],
            'active' => $request->boolean('active'),
        ];

        if (! empty($validated['password'])) {
            $updateData['password'] = $validated['password'];
        }

        $user->update($updateData);

        return redirect()
            ->route('users.index')
            ->with('success', 'Application user updated successfully.');
    }
}