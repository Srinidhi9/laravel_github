<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Borrow;
use App\Models\Book;

class BorrowController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * List all borrow records
     * Students see only their own borrows, Admin/Librarian see all
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'student') {
            $borrows = Borrow::where('student_id', $user->id)
                ->with('book', 'handler')
                ->get();
        } else {
            $borrows = Borrow::with('book', 'student', 'handler')->get();
        }

        return response()->json(['borrows' => $borrows], 200);
    }

    /**
     * Student borrows a book
     */
    public function borrow(Request $request, Book $book)
    {
        $user = $request->user();

        if ($user->role !== 'student') {
            return response()->json(['message' => 'Only students can borrow books'], 403);
        }

        
        // Check stock
        if ($book->number_of_available_items == 0) {
            return response()->json(['message' => 'Book is not available'], 400);
        }

        // Already borrowed by same student
        $existingBorrow = Borrow::where('book_id', $book->id)
            ->where('student_id', $user->id)
            ->where('status', 'borrowed')
            ->first();

        if ($existingBorrow) {
            return response()->json(['message' => 'You have already borrowed this book'], 400);
        }

        
        // Borrow the book
        $borrow = Borrow::create([
            'book_id' => $book->id,
            'student_id' => $user->id,
            'status' => 'borrowed',
            'borrowed_at' => now(),
            'handled_by' => null,
        ]);

        // Decrease available items
        $book->decrement('number_of_available_items');

        // Load relationships before returning
        $borrow->load('book', 'student', 'handler');

        return response()->json([
            'message' => 'Book borrowed successfully',
            'borrow' => $borrow,
            'available_items_left' => $book->number_of_available_items
        ], 201);
    }


    /**
     * Student returns a book
     */
    public function return(Request $request, Book $book)
    {
        $user = $request->user();

        if ($user->role !== 'student') {
            return response()->json(['message' => 'Only students can return books'], 403);
        }

        $borrow = Borrow::where('book_id', $book->id)
            ->where('student_id', $user->id)
            ->where('status', 'borrowed')
            ->first();

        // Mark as returned
        $borrow->update([
            'status' => 'returned',
            'returned_at' => now(),
        ]);


        if (!$borrow) {
            return response()->json(['message' => 'You have not borrowed this book or already returned it'], 400);
        }
        

        // Increment available items
        $book->increment('number_of_available_items');

        return response()->json([
            'message' => 'Book returned successfully',
            'borrow' => $borrow,
            'available_items_left' => $book->number_of_available_items
        ], 200);
    }

    public function addRemarks(Request $request, Borrow $borrow)
    {
        $user = $request->user();

        if (!in_array($user->role, ['admin', 'librarian'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'remarks' => 'required|string|max:255',
        ]);

        $borrow->update([
            'remarks' => $request->remarks,
            'handled_by' => $user->id,
        ]);

        return response()->json([
            'message' => 'Remarks added successfully',
            'borrow'  => $borrow
        ], 200);
    }

    /**
     * Admin/Librarian manually creates a borrow record
     */
    public function store(Request $request)
    {
        $user = $request->user();

        if (!in_array($user->role, ['admin', 'librarian'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            
            'book_id' => 'required|exists:books,id',
            'student_id' => 'required|exists:users,id',
            'remarks' => 'nullable|string',
        ]);
        

        $book = Book::findOrFail($request->book_id);

        if ($book->number_of_available_items == 0) {
            return response()->json(['message' => 'Book is not available'], 400);
        }

        $borrow = Borrow::create([
            'book_id' => $book->id,
            'student_id' => $request->student_id,
            'status' => 'borrowed',
            'borrowed_at' => now(),
            'handled_by' => $user->id,
            'remarks' => $request->remarks,
        ]);

        // Decrease available items
        $book->decrement('number_of_available_items');

        return response()->json(['message' => 'Borrow record created', 'borrow' => $borrow], 201);
    }

    /**
     * Admin/Librarian marks book as returned
     */
    public function update(Request $request, Borrow $borrow)
    {
        $user = $request->user();

        if (!in_array($user->role, ['admin', 'librarian'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($borrow->status === 'returned') {
            return response()->json(['message' => 'Book already returned'], 400);
        }

        $borrow->update([
            'status' => 'returned',
            'returned_at' => now(),
            'handled_by' => $user->id,
            'remarks' => $request->remarks ?? $borrow->remarks,
        ]);

        // Increment book stock
        $borrow->book->increment('available_items');

        return response()->json(['message' => 'Book returned successfully', 'borrow' => $borrow], 200);
    }

    /**
     * Show single borrow record
     */
    public function show(Request $request, Borrow $borrow)
    {
        $user = $request->user();

        if ($user->role === 'student' && $borrow->student_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $borrow->load('book', 'student', 'handler');

        return response()->json(['borrow' => $borrow], 200);
    }
}
