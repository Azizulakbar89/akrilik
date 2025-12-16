<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserOwnerController extends Controller
{
    public function index()
    {
        $users = User::where('role', '!=', 'owner')->get();
        return view('owner.users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:admin,kasir'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        return response()->json(['success' => 'User berhasil ditambahkan']);
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        return response()->json($user);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', 'in:admin,kasir'],
        ];

        if ($request->email != $user->email) {
            $rules['email'] = ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class];
        }

        $validated = $request->validate($rules);

        $user->name = $validated['name'];
        $user->role = $validated['role'];

        if (isset($validated['email'])) {
            $user->email = $validated['email'];
        }

        if ($request->filled('password')) {
            $request->validate([
                'password' => ['confirmed', Rules\Password::defaults()],
            ]);
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return response()->json(['success' => 'User berhasil diperbarui']);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Prevent owner from deleting themselves
        if ($user->role === 'owner') {
            return response()->json(['error' => 'Tidak dapat menghapus owner'], 403);
        }

        $user->delete();

        return response()->json(['success' => 'User berhasil dihapus']);
    }
}
