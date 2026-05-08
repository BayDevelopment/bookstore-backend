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

        $books = BookModel::query()
            ->when(
                $search,
                fn($q) =>
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('author', 'like', "%{$search}%")
            )
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return response()->json([
            'data' => collect($books->items())->map(fn($b) => [
                'id'     => $b->id,
                'title'  => $b->title,
                'author' => $b->author,
                'price'  => $b->price,
                'cover'  => $b->cover ? asset('storage/' . $b->cover) : null,
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
                'type'        => $book->type,
                'price'       => $book->price,
                'stock'       => $book->stock,
                'description' => $book->description,
                'cover'       => $book->cover ? asset('storage/' . $book->cover) : null,
            ]
        ]);
    }
}
