<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BookModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search', '');
        $categoryId = $request->get('category_id');

        $books = BookModel::query()
            ->when($search, function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('author', 'like', "%{$search}%");
            })
            ->when($categoryId, function ($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        $books->getCollection()->transform(fn($b) => [
            'id'          => $b->id,
            'title'       => $b->title,
            'author'      => $b->author,
            'has_print'   => $b->has_print,
            'price_print' => $b->price_print,
            'has_pdf'     => $b->has_pdf,
            'price_pdf'   => $b->price_pdf,
            'stock'       => $b->stock,
            'cover'       => $b->cover ? asset('storage/' . $b->cover) : null,
            'views'       => $b->views ?? 0,
        ]);

        return response()->json($books);
    }

    public function show($id)
    {
        // ❌ HAPUS increment di sini — biarkan show hanya return data
        $book = BookModel::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id'          => $book->id,
                'title'       => $book->title,
                'author'      => $book->author,
                'has_print'   => $book->has_print,
                'price_print' => $book->price_print,
                'has_pdf'     => $book->has_pdf,
                'price_pdf'   => $book->price_pdf,
                'stock'       => $book->stock,
                'file_path'   => $book->file_path,
                'description' => $book->description,
                'cover'       => $book->cover ? asset('storage/' . $book->cover) : null,
                'views'       => $book->views,
            ]
        ]);
    }

    public function incrementViewCount(Request $request, $id)
    {
        $book = BookModel::findOrFail($id);

        // Throttle: 1 view per user/guest per buku per jam
        // Kunci unik berdasarkan: user ID (jika login) atau IP (jika guest)
        $identifier = $request->user()
            ? 'user_' . $request->user()->id
            : 'guest_' . $request->ip();

        $sessionKey = "viewed_book_{$id}_{$identifier}";

        // Cek apakah sudah dilihat dalam 1 jam terakhir (pakai cache)
        if (!Cache::has($sessionKey)) {
            $book->increment('views');
            // Simpan ke cache selama 60 menit
            Cache::put($sessionKey, true, now()->addMinutes(60));
        }

        return response()->json([
            'success' => true,
            'views'   => $book->fresh()->views,
        ]);
    }
}
