<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Borrow;
use Illuminate\Http\Request;

class BorrowController extends Controller
{
    public function index()
    {
        return response()->json(['success' => true, 'data' => Borrow::with('book')->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['book_id' => 'required|exists:books,id']);
        $book = Book::find($data['book_id']);

        if (!$book->available) {
            return response()->json(['success' => false, 'message' => 'Livre non disponible'], 422);
        }

        $borrow = Borrow::create([
            'user_name' => $request->user()->name,
            'book_id' => $book->id,
            'borrowed_at' => now(),
        ]);
        $book->update(['available' => false]);

        return response()->json(['success' => true, 'data' => $borrow], 201);
    }

    public function return($id)
    {
        $borrow = Borrow::find($id);
        if (!$borrow) return response()->json(['success' => false, 'message' => 'Emprunt introuvable'], 404);
        if ($borrow->returned_at) return response()->json(['success' => false, 'message' => 'Déjà retourné'], 422);

        $borrow->update(['returned_at' => now()]);
        $borrow->book->update(['available' => true]);
        return response()->json(['success' => true, 'data' => $borrow]);
    }
}