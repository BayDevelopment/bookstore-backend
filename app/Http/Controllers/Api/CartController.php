<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BookModel;
use App\Models\CartModel;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function count(Request $request)
    {
        $count = CartModel::where('user_id', $request->user()->id)->count();
        return response()->json(['count' => $count]);
    }

    public function index(Request $request)
    {
        $cart = CartModel::where('user_id', $request->user()->id)
            ->with(['book' => fn($q) => $q->select('id', 'title', 'author', 'cover', 'has_print', 'has_pdf', 'price_print', 'price_pdf', 'stock')])
            ->get()
            ->map(fn($item) => $this->formatCartItem($item));

        return response()->json(['data' => $cart]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'book_id'  => 'required|exists:books,id',
            'quantity' => 'required|integer|min:1|max:10',
            'type'     => 'required|in:print,pdf',
        ]);

        $book = BookModel::findOrFail($request->book_id);

        if ($request->type === 'print') {
            if (!$book->has_print) {
                return response()->json(['message' => 'Buku cetak tidak tersedia'], 422);
            }
            if ($book->stock < $request->quantity) { // ✅ Cek langsung dengan quantity
                return response()->json(['message' => 'Stok tidak mencukupi'], 422);
            }
        }

        if ($request->type === 'pdf' && !$book->has_pdf) {
            return response()->json(['message' => 'PDF tidak tersedia'], 422);
        }

        $existing = CartModel::where('user_id', $request->user()->id)
            ->where('book_id', $request->book_id)
            ->where('type', $request->type)
            ->first();

        $newQty = ($existing->qty ?? 0) + $request->quantity;

        if ($request->type === 'print' && $newQty > $book->stock) {
            return response()->json(['message' => 'Jumlah melebihi stok tersedia'], 422);
        }

        $cart = CartModel::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'book_id' => $request->book_id,
                'type'    => $request->type,
            ],
            ['qty' => $newQty]
        );

        $cart->load('book');

        return response()->json([
            'message' => 'Berhasil ditambahkan',
            'data'    => $this->formatCartItem($cart)
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:10',
        ]);

        $cart = CartModel::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        if ($cart->type === 'print') {
            $book = BookModel::findOrFail($cart->book_id);
            if ($request->quantity > $book->stock) {
                return response()->json(['message' => 'Jumlah melebihi stok tersedia'], 422);
            }
        }

        $cart->update(['qty' => $request->quantity]);
        $cart->load('book');

        return response()->json([
            'message' => 'Updated',
            'data'    => $this->formatCartItem($cart)
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $deleted = CartModel::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->delete();

        if (!$deleted) {
            return response()->json(['message' => 'Item tidak ditemukan'], 404);
        }

        return response()->json(['message' => 'Dihapus']);
    }

    public function clear(Request $request)
    {
        CartModel::where('user_id', $request->user()->id)->delete();
        return response()->json(['message' => 'Keranjang dikosongkan']);
    }

    // ✅ Helper untuk format response konsisten
    private function formatCartItem(CartModel $item): array
    {
        $price = $item->book->priceFor($item->type);

        return [
            'id'       => $item->id,
            'qty'      => $item->qty,
            'type'     => $item->type,
            'price'    => $price,
            'subtotal' => $price ? $price * $item->qty : 0,
            'book'     => [
                'id'          => $item->book->id,
                'title'       => $item->book->title,
                'author'      => $item->book->author,
                'cover'       => $item->book->cover ? asset('storage/' . $item->book->cover) : null,
                'has_print'   => $item->book->has_print,
                'has_pdf'     => $item->book->has_pdf,
                'price_print' => $item->book->price_print,
                'price_pdf'   => $item->book->price_pdf,
                'stock'       => $item->book->stock,
            ],
        ];
    }
}
