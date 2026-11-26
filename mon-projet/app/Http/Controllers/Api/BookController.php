<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index()
    {
        return response()->json(['success' => true, 'data' => Book::with('author')->get()]);
    }

    public function show($id)
    {
        $book = Book::with('author')->find($id);
        if (!$book) return response()->json(['success' => false, 'message' => 'Livre introuvable'], 404);
        return response()->json(['success' => true, 'data' => $book]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'isbn' => 'required|string|unique:books',
            'year' => 'required|integer',
            'author_id' => 'required|exists:authors,id',
        ]);
        return response()->json(['success' => true, 'data' => Book::create($data)], 201);
    }

    public function update(Request $request, $id)
    {
        $book = Book::find($id);
        if (!$book) return response()->json(['success' => false, 'message' => 'Livre introuvable'], 404);
        $data = $request->validate([
            'title' => 'sometimes|string',
            'isbn' => 'sometimes|string|unique:books,isbn,' . $book->id,
            'year' => 'sometimes|integer',
            'author_id' => 'sometimes|exists:authors,id',
        ]);
        $book->update($data);
        return response()->json(['success' => true, 'data' => $book]);
    }

    public function destroy($id)
    {
        $book = Book::find($id);
        if (!$book) return response()->json(['success' => false, 'message' => 'Livre introuvable'], 404);
        $book->delete();
        return response()->json(['success' => true, 'message' => 'Supprimé']);
    }
}