<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BookModel;
use App\Models\OrderItemModel;
use App\Models\OrderModel;
use App\Models\PaymentModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = OrderModel::with(['items.book', 'paymentMethod'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json(['data' => $orders]);
    }

    // POST /api/orders
    public function store(Request $request)
    {
        $validated = $request->validate([
            'book_id'           => 'required|exists:books,id',
            'quantity'          => 'required|integer|min:1',
            'payment_method_id' => 'required|exists:payment_methods,id',
        ]);

        $book = BookModel::findOrFail($validated['book_id']);

        if ($book->stock < $validated['quantity']) {
            return response()->json(['message' => 'Stok tidak mencukupi.'], 422);
        }

        $order = null;

        try {
            DB::transaction(function () use ($validated, $book, &$order) {
                $order = OrderModel::create([
                    'user_id'           => Auth::id(),
                    'payment_method_id' => $validated['payment_method_id'],
                    'quantity'          => $validated['quantity'],
                    'total'             => $book->price * $validated['quantity'],
                    'status'            => 'pending',
                ]);

                OrderItemModel::create([
                    'order_id' => $order->id,
                    'book_id'  => $book->id,
                    'qty'      => $validated['quantity'],
                    'price'    => $book->price,
                ]);

                $book->decrement('stock', $validated['quantity']);
            });
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Gagal membuat pesanan.',
                'debug'   => $e->getMessage(), // hapus saat production
            ], 500);
        }

        return response()->json(['data' => $order->load('items.book')], 201);
    }

    // GET /api/orders/{id}
    public function show(Request $request, $id)
    {
        $order = OrderModel::with(['items.book', 'paymentMethod'])
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);

        return response()->json(['data' => $order]);
    }

    // POST /api/orders/{id}/payment
    public function uploadPayment(Request $request, $id)
    {
        $request->validate([
            'payment_proof' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $order = OrderModel::where('user_id', $request->user()->id)
            ->findOrFail($id);

        if ($order->paymentMethod?->code === 'cash') {
            return response()->json([
                'message' => 'Metode bayar di tempat tidak memerlukan bukti pembayaran.',
            ], 422);
        }

        if ($order->status !== 'pending') {
            return response()->json([
                'message' => 'Order sudah diproses, tidak bisa upload bukti.',
            ], 422);
        }

        $path = $request->file('payment_proof')->store('payment_proofs', 'public');

        $order->update([
            'payment_proof' => $path,
            'proof_status'  => 'uploaded',
        ]);

        return response()->json(['data' => $order->load(['items.book', 'paymentMethod'])]);
    }
}
