<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\BorrowController;
use App\Http\Controllers\Api\LibrarianRequestController;




Route::prefix('v1')->group(function () {


   
    // AUTH ROUTES (PUBLIC)
    Route::post('/login', [AuthController::class, 'login']);

    //FORGOT PASSWORD
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

    // Logout route (requires authentication)
    Route::middleware('auth:api')->post('/logout', [AuthController::class, 'logout']);


    // USER MANAGEMENT
    Route::middleware(['auth:api', 'role:admin,librarian'])->group(function () {
        // List all users
        Route::get('/users', [UserController::class, 'index']);

        // Show a single user
        Route::get('/users/{user}', [UserController::class, 'show']);

        // Register a new user (Admin/Librarian)
        Route::post('/users/register', [UserController::class, 'registerUser']);
    });



    // LIBRARIAN REQUESTS

    Route::middleware('auth:api')->group(function () {
        // Librarian submits request
        Route::post('/librarian/request', [LibrarianRequestController::class, 'request']);

        //Librarian check request status
        Route::middleware('auth:api')->get('/librarian/requests/my', [LibrarianRequestController::class, 'myRequests']);


        // Admin views pending requests
        Route::get('/librarian/requests/pending', [LibrarianRequestController::class, 'pendingRequests']);

        // Admin approves a request
        Route::post('/librarian/request/{id}/approve', [LibrarianRequestController::class, 'approve']);

        // Admin rejects a request
        Route::post('/librarian/request/{id}/reject', [LibrarianRequestController::class, 'reject']);
    });




    // BOOK MANAGEMENT
    Route::middleware('auth:api')->group(function () {
        Route::get('/books', [BookController::class, 'index']);
        Route::get('/books/{id}', [BookController::class, 'show']);
    });

    // Admin/Librarian only → manage books

    Route::middleware(['auth:api', 'role:librarian,admin'])->group(function () {
        Route::post('/books', [BookController::class, 'store']);
        Route::put('/books/{id}', [BookController::class, 'update']);
        Route::delete('/books/{id}', [BookController::class, 'destroy']);
    });


    // BORROW MANAGEMENT 

    Route::middleware('auth:api')->group(function () {
        // Student actions
        Route::post('/borrow/{book}', [BorrowController::class, 'borrow'])->middleware(['auth:api', 'role:student']);
        Route::post('/return/{book}', [BorrowController::class, 'return'])->middleware('role:student');

        // Admin/Librarian actions
        Route::post('/borrow', [BorrowController::class, 'store'])->middleware('role:admin,librarian');
        Route::put('/borrow/{borrow}', [BorrowController::class, 'update'])->middleware('role:admin,librarian');

        // View borrow records
        Route::get('/borrows', [BorrowController::class, 'index']);
        Route::get('/borrows/{borrow}', [BorrowController::class, 'show']);
    });

    // Librarian/Admin can add remarks to a borrow record
    Route::post('/borrows/{borrow}/remarks', [BorrowController::class, 'addRemarks'])
        ->middleware(['auth:api', 'role:librarian,admin']);
});
