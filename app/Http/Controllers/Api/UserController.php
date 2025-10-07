<?php

namespace App\Http\Controllers\Api;

use App\Models\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index(Request $request)
    {
        $currentUser = $request->user();
        if (!in_array($currentUser->role, ['admin', 'librarian'])) {
            return response()->json(['message' => 'Unauthorized. Only admin or librarian can view users.'], 403);
        }
        $users = User::all();
        return response()->json(User::all());
    }

    public function show(Request $request, User $user)
    {
        $currentUser = $request->user();
        if (!in_array($currentUser->role, ['admin', 'librarian'])) {
            return response()->json(['message' => 'Unauthorized. Only admin or librarian can view user details.'], 403);
        }
        return response()->json(['user' => $user], 200);
    }

    public function registerUser(Request $request)
    {
        $currentUser = $request->user();
        if (!in_array($currentUser->role, ['admin', 'librarian'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,librarian,student',
            'phone_number' => 'nullable|string|max:15',
        ]);

        if ($currentUser->role === 'librarian' && $request->role === 'admin') {
            return response()->json(['message' => 'Librarians cannot register admins.'], 403);
        }

        if ($currentUser->role === 'librarian' && $request->role === 'librarian') {
            return response()->json(['message' => 'Librarians need admin approval to register another librarian.'], 403);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'phone_number' => $request->phone_number,
            'created_by' => $currentUser->id,
        ]);

        return response()->json(['message' => 'User registered successfully', 'user' => $user], 201);
    }

    public function update(Request $request, User $user)
    {
        $currentUser = $request->user();
        if (!in_array($currentUser->role, ['admin', 'librarian'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
            'role' => 'sometimes|required|in:admin,librarian,student',
            'phone_number' => 'nullable|string|max:15',
        ]);

        // Update user and track updater
        $user->update(array_merge($request->all(), ['updated_by' => $currentUser->id]));

        return response()->json(['message' => 'User updated successfully', 'user' => $user], 200);
    }
}
