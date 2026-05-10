<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BookModel;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search', '');
        $categoryId = $request->get('category_id');

        $books = BookModel::query()
            ->when(
                $search,
                fn($q) =>
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('author', 'like', "%{$search}%")
            )
            ->when(
                $categoryId,
                fn($q) =>
                $q->where('category_id', $categoryId)
            )
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return response()->json([
            'data' => collect($books->items())->map(fn($b) => [
                'id'          => $b->id,
                'title'       => $b->title,
                'author'      => $b->author,
                // ✅ Field baru
                'has_print'   => $b->has_print,
                'price_print' => $b->price_print,
                'has_pdf'     => $b->has_pdf,
                'price_pdf'   => $b->price_pdf,
                'stock'       => $b->stock,
                'cover'       => $b->cover ? asset('storage/' . $b->cover) : null,
            ]),
            'meta' => [
                'current_page' => $books->currentPage(),
                'last_page'    => $books->lastPage(),
                'total'        => $books->total(),
            ],
        ]);
    }

    public function show($id)
    {
        $book = BookModel::findOrFail($id);

        return response()->json([
            'data' => [
                'id'          => $book->id,
                'title'       => $book->title,
                'author'      => $book->author,
                // ✅ Field baru
                'has_print'   => $book->has_print,
                'price_print' => $book->price_print,
                'has_pdf'     => $book->has_pdf,
                'price_pdf'   => $book->price_pdf,
                'stock'       => $book->stock,
                'file_path'   => $book->file_path,
                'description' => $book->description,
                'cover'       => $book->cover ? asset('storage/' . $book->cover) : null,
            ]
        ]);
    }
}
