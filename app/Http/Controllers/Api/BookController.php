<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Book;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BookController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index()
    {
        $books = Book::all();
        return response()->json(['success' => true, 'data' => $books], 200);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['admin', 'librarian'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'isbn' => 'required|string|unique:books,isbn',
            'description' => 'nullable|string',
            'status' => 'nullable|in:available,borrowed',
            'published_date' => 'nullable|date',
            'published_by' => 'nullable|string|max:255',
            'number_of_copies_sold' => 'nullable|integer',
            'genre' => 'nullable|string|max:255',
            'number_of_available_items' => 'nullable|integer',
            'language' => 'nullable|string|max:255',
            'edition' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        $data['number_of_copies_sold'] = $data['number_of_copies_sold'] ?? 0;
        $data['number_of_available_items'] = $data['number_of_available_items'] ?? 1;
        $data['status'] = $data['status'] ?? 'available';
        $book = Book::create(array_merge($request->all(), ['created_by' => $user->id]));

        return response()->json(['success' => true, 'data' => $book], 201);
    }

    public function show($id)
    {
        $book = Book::find($id);
        if (!$book)
            return response()->json(['message' => 'Book not found'], 404);

        return response()->json(['success' => true, 'data' => $book], 200);
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['admin', 'librarian'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $book = Book::find($id);
        if (!$book)
            return response()->json(['message' => 'Book not found'], 404);

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'author' => 'sometimes|required|string|max:255',
            'isbn' => 'sometimes|required|string|unique:books,isbn,' . $id,
            'description' => 'nullable|string',
            'status' => 'nullable|in:available,borrowed',
            'published_date' => 'nullable|date',
            'published_by' => 'nullable|string|max:255',
            'number_of_copies_sold' => 'nullable|integer',
            'genre' => 'nullable|string|max:255',
            'number_of_available_items' => 'nullable|integer',
            'language' => 'nullable|string|max:255',
            'edition' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }


        $book->update(array_merge($request->all(), ['updated_by' => $user->id]));

        $book->refresh();

        return response()->json([
            'success' => true,
            'data' => $book
        ], 200);
    }

    public function destroy($id)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['admin', 'librarian'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        dd($user);
        $updatedBook = Book::find($id);
        return response()->json(['success' => true, 'data' => $updatedBook], 200);
    }
}
