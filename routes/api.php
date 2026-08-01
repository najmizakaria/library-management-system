<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\BookController;
use App\Http\Controllers\API\BorrowController;

// Public Routes (Students & Staff Authentication)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected Routes (Requires Auth Token from Flutter)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Book Catalog Endpoints
    Route::get('/books', [BookController::class, 'index']);
    Route::get('/books/{id}', [BookController::class, 'show']);

    // Student Borrow Requests
    Route::post('/borrow-requests', [BorrowController::class, 'store']);
    Route::get('/my-borrow-requests', [BorrowController::class, 'userRequests']);

    // Staff / Admin Only Workflows
    Route::get('/staff/pending-requests', [BorrowController::class, 'pendingRequests']);
    Route::put('/staff/borrow-requests/{id}/status', [BorrowController::class, 'updateStatus']);
    
    // Admin Only Book Management
    Route::post('/admin/books', [BookController::class, 'store']);
    Route::put('/admin/books/{id}', [BookController::class, 'update']);
    Route::delete('/admin/books/{id}', [BookController::class, 'destroy']);
});