<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\BorrowRequest;
use App\Models\Book;
use Illuminate\Http\Request;

class BorrowController extends Controller
{
    // Student submits a request
    public function store(Request $request)
    {
        $validated = $request->validate([
            'book_id' => 'required|exists:books,id',
            'request_date' => 'required|date',
        ]);

        $book = Book::findOrFail($validated['book_id']);

        if ($book->available_copies < 1) {
            return response()->json(['message' => 'No copies available currently.'], 400);
        }

        $borrowRequest = BorrowRequest::create([
            'user_id' => $request->user()->id,
            'book_id' => $validated['book_id'],
            'status' => 'pending',
            'request_date' => $validated['request_date'],
        ]);

        return response()->json($borrowRequest, 201);
    }

    // Student checks their requests
    public function userRequests(Request $request)
    {
        return response()->json(
            BorrowRequest::with('book')
                ->where('user_id', $request->user()->id)
                ->get()
        );
    }

    // Staff views pending requests
    public function pendingRequests()
    {
        return response()->json(
            BorrowRequest::with(['user', 'book'])
                ->where('status', 'pending')
                ->get()
        );
    }

    // Staff approves or rejects request
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected,borrowed,returned',
            'due_date' => 'nullable|date',
        ]);

        $borrowRequest = BorrowRequest::findOrFail($id);
        $borrowRequest->status = $validated['status'];
        $borrowRequest->approved_by = $request->user()->id;

        if (isset($validated['due_date'])) {
            $borrowRequest->due_date = $validated['due_date'];
        }

        // Adjust book stock on approval/return
        if ($validated['status'] === 'approved') {
            $borrowRequest->book->decrement('available_copies');
        } elseif ($validated['status'] === 'returned') {
            $borrowRequest->book->increment('available_copies');
            $borrowRequest->returned_at = now();
        }

        $borrowRequest->save();

        return response()->json($borrowRequest);
    }
}