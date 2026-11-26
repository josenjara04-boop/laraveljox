<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Services\OpenAIService;
use Illuminate\Http\Request;
use RuntimeException;

class BookSummaryController extends Controller
{
    public function __construct(private OpenAIService $openai) {}

    public function store(int $id)
    {
        $book = Book::with('author')->find($id);
        if (!$book) {
            return response()->json(['success' => false, 'message' => 'Livre introuvable'], 404);
        }

        if ($book->summary) {
            return response()->json([
                'success' => true,
                'data' => $this->format($book),
                'message' => 'Résumé existant retourné (pas de nouvel appel API)',
            ]);
        }

        try {
            $summary = $this->openai->generateSummary(
                $book->title,
                $book->author->first_name . ' ' . $book->author->last_name,
                $book->year
            );
        } catch (RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 503);
        }

        $book->update(['summary' => $summary]);

        return response()->json(['success' => true, 'data' => $this->format($book)]);
    }

    public function show(int $id)
    {
        $book = Book::find($id);
        if (!$book) {
            return response()->json(['success' => false, 'message' => 'Livre introuvable'], 404);
        }
        if (!$book->summary) {
            return response()->json(['success' => false, 'message' => 'Aucun résumé pour ce livre'], 404);
        }

        return response()->json(['success' => true, 'data' => $this->format($book)]);
    }

    public function smartSearch(Request $request)
    {
        $data = $request->validate(['query' => 'required|string|min:3|max:500']);

        try {
            $keywords = $this->openai->extractKeywords($data['query']);
        } catch (RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 503);
        }

        if (empty($keywords)) {
            return response()->json([
                'success' => true,
                'query' => $data['query'],
                'keywords' => [],
                'data' => [],
            ]);
        }

        $books = Book::with('author')
            ->where(function ($q) use ($keywords) {
                foreach ($keywords as $kw) {
                    $q->orWhere('title', 'like', "%{$kw}%")
                      ->orWhere('summary', 'like', "%{$kw}%");
                }
            })
            ->get();

        return response()->json([
            'success' => true,
            'query' => $data['query'],
            'keywords' => $keywords,
            'data' => $books,
        ]);
    }

    private function format(Book $book): array
    {
        return [
            'book_id' => $book->id,
            'title' => $book->title,
            'summary' => $book->summary,
            'generated_by' => $this->openai->model(),
            'generated_at' => $book->updated_at->toIso8601String(),
        ];
    }
}
