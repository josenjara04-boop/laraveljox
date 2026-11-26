<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Author;
use Illuminate\Http\Request;

class AuthorController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => Author::with('books')->get(),
        ]);
    }

    public function show($id)
    {
        $author = Author::with('books')->find($id);
        if (!$author) {
            return response()->json(['success' => false, 'message' => 'Auteur introuvable'], 404);
        }
        return response()->json(['success' => true, 'data' => $author]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name'  => 'required|string|max:255',
            'last_name'   => 'required|string|max:255',
            'nationality' => 'nullable|string|max:255',
        ]);
        return response()->json(['success' => true, 'data' => Author::create($data)], 201);
    }

    public function update(Request $request, $id)
    {
        $author = Author::find($id);
        if (!$author) {
            return response()->json(['success' => false, 'message' => 'Auteur introuvable'], 404);
        }
        $data = $request->validate([
            'first_name'  => 'sometimes|string|max:255',
            'last_name'   => 'sometimes|string|max:255',
            'nationality' => 'nullable|string|max:255',
        ]);
        $author->update($data);
        return response()->json(['success' => true, 'data' => $author]);
    }

    public function destroy($id)
    {
        $author = Author::find($id);
        if (!$author) {
            return response()->json(['success' => false, 'message' => 'Auteur introuvable'], 404);
        }
        $author->delete();
        return response()->json(['success' => true, 'message' => 'Supprimé']);
    }
}