<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BookModel;
use App\Models\OrderModel;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = OrderModel::with('book')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json(['data' => $orders]);
    }

    // POST /api/orders — buat pesanan baru
    public function store(Request $request)
    {
        $request->validate(['book_id' => 'required|exists:books,id']);

        $book = BookModel::findOrFail($request->book_id);

        $order = OrderModel::create([
            'user_id'      => $request->user()->id,
            'book_id'      => $request->book_id,
            'total'        => $book->price,
            'status'       => 'pending',
            'proof_status' => 'not_uploaded',
        ]);

        return response()->json(['data' => $order->load('book')], 201);
    }

    // GET /api/orders/{id} — detail pesanan
    public function show(Request $request, $id)
    {
        $order = OrderModel::with('book')
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);

        return response()->json(['data' => $order]);
    }

    // POST /api/orders/{id}/payment — upload bukti pembayaran
    public function uploadPayment(Request $request, $id)
    {
        $request->validate([
            'payment_proof' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120'
        ]);

        $order = OrderModel::where('user_id', $request->user()->id)->findOrFail($id);

        $path = $request->file('payment_proof')->store('payment_proofs', 'public');

        $order->update([
            'payment_proof' => $path,
            'proof_status'  => 'uploaded', // menunggu verifikasi admin
        ]);

        return response()->json(['data' => $order->load('book')]);
    }
}
