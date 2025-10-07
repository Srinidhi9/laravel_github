<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\LibrarianRequest;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class LibrarianRequestController extends Controller
{
    // Librarian submits request
    public function request(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'librarian') {
            return response()->json(['message' => 'Only librarians can request new librarians'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'phone_number' => 'nullable|string|max:15',
        ]);

        $librarianRequest = LibrarianRequest::create([
            'requested_by' => $user->id,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone_number' => $request->phone_number,
        ]);

        return response()->json([
            'message' => 'Request submitted. Waiting for admin approval.',
            'request' => $librarianRequest
        ], 201);
    }

    // Admin views all pending requests
    public function pendingRequests(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Only admins can view requests'], 403);
        }

        $requests = LibrarianRequest::where('status', 'pending')->get();

        return response()->json([
            'requests' => $requests
        ], 200);
    }

    // Admin approves request
    public function approve(Request $request, $id)
    {
        $admin = $request->user();
        if ($admin->role !== 'admin') {
            return response()->json(['message' => 'Only admins can approve requests'], 403);
        }

        $librarianRequest = LibrarianRequest::find($id);
        if (!$librarianRequest || $librarianRequest->status !== 'pending') {
            return response()->json(['message' => 'Request not found or already processed'], 404);
        }

        // Create new Librarian user
        $newLibrarian = User::create([
            'name' => $librarianRequest->name,
            'email' => $librarianRequest->email,
            'password' => $librarianRequest->password,
            'role' => 'librarian',
            'phone_number' => $librarianRequest->phone_number,
            'created_by' => $admin->id,
        ]);

        $librarianRequest->update([
            'status' => 'approved',
            'approved_by' => $admin->id
        ]);

        return response()->json([
            'message' => 'Librarian request approved',
            'librarian' => $newLibrarian
        ], 200);
    }

    // Admin rejects request
    public function reject(Request $request, $id)
    {
        $admin = $request->user();
        if ($admin->role !== 'admin') {
            return response()->json(['message' => 'Only admins can reject requests'], 403);
        }

        $librarianRequest = LibrarianRequest::find($id);
        if (!$librarianRequest || $librarianRequest->status !== 'pending') {
            return response()->json(['message' => 'Request not found or already processed'], 404);
        }

        $librarianRequest->update([
            'status' => 'rejected',
            'approved_by' => $admin->id
        ]);

        return response()->json([
            'message' => 'Librarian request rejected'
        ], 200);
    }

    public function myRequests(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'librarian') {
            return response()->json(['message' => 'Only librarians can view their requests'], 403);
        }

        $requests = LibrarianRequest::where('requested_by', $user->id)->get();

        return response()->json([
            'requests' => $requests
        ], 200);
    }
}
