<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BookModel;
use App\Models\CartModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            ->with('book')
            ->get();
        return response()->json(['data' => $cart]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'book_id'  => 'required|exists:books,id',
            'quantity' => 'required|integer|min:1|max:10',
        ]);

        // Cek stok tersedia
        $book = BookModel::findOrFail($request->book_id);
        if ($book->stock < 1) {
            return response()->json(['message' => 'Stok buku habis'], 422);
        }

        // Cek qty di cart tidak melebihi stok
        $existing = CartModel::where('user_id', $request->user()->id)
            ->where('book_id', $request->book_id)
            ->first();

        $newQty = ($existing->qty ?? 0) + $request->quantity;
        if ($newQty > $book->stock) {
            return response()->json(['message' => 'Jumlah melebihi stok tersedia'], 422);
        }

        $cart = CartModel::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'book_id' => $request->book_id,
            ],
            [
                'qty' => DB::raw('qty + ' . (int) $request->quantity),
            ]
        );

        return response()->json(['message' => 'Berhasil ditambahkan', 'data' => $cart]);
    }

    public function destroy(Request $request, $id)
    {
        CartModel::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->delete();

        return response()->json(['message' => 'Dihapus']);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:10',
        ]);

        $cart = CartModel::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        // Cek stok
        $book = BookModel::findOrFail($cart->book_id);
        if ($request->quantity > $book->stock) {
            return response()->json(['message' => 'Jumlah melebihi stok tersedia'], 422);
        }

        $cart->update(['qty' => $request->quantity]);

        return response()->json(['message' => 'Updated']);
    }
}
