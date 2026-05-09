<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\OrderConfirmationMail;
use App\Models\BookModel;
use App\Models\CartModel;
use App\Models\OrderItemModel;
use App\Models\OrderModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'items'             => 'required|array|min:1',
            'items.*.book_id'   => 'required|exists:books,id',
            'items.*.quantity'  => 'required|integer|min:1|max:10',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'from_cart'         => 'boolean',
        ]);

        $order = null;

        try {
            DB::transaction(function () use ($validated, $request, &$order) {
                $total = 0;
                $books = [];

                foreach ($validated['items'] as $item) {
                    $book = BookModel::lockForUpdate()->findOrFail($item['book_id']);

                    if ($book->stock < $item['quantity']) {
                        throw new \Exception("Stok buku \"{$book->title}\" tidak mencukupi.");
                    }

                    $total += $book->price * $item['quantity'];
                    $books[] = ['book' => $book, 'quantity' => $item['quantity']];
                }

                $order = OrderModel::create([
                    'user_id'           => $request->user()->id,
                    'payment_method_id' => $validated['payment_method_id'],
                    'total'             => $total,
                    'status'            => 'pending',
                ]);

                foreach ($books as $entry) {
                    OrderItemModel::create([
                        'order_id' => $order->id,
                        'book_id'  => $entry['book']->id,
                        'qty'      => $entry['quantity'],
                        'price'    => $entry['book']->price,
                    ]);

                    $entry['book']->decrement('stock', $entry['quantity']);
                }

                if (!empty($validated['from_cart'])) {
                    CartModel::where('user_id', $request->user()->id)->delete();
                }
            });

            Mail::to($request->user()->email)
                ->queue(new OrderConfirmationMail(
                    $order->load(['items.book', 'paymentMethod', 'user'])
                ));

            return response()->json(['data' => $order->load('items.book')], 201);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function show(Request $request, $id)
    {
        $order = OrderModel::with(['items.book', 'paymentMethod'])
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);

        return response()->json(['data' => $order]);
    }

    public function uploadPayment(Request $request, $id)
    {
        $request->validate([
            'payment_proof' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png',
                'max:2048',
                function ($attribute, $value, $fail) {
                    $mime = $value->getMimeType();
                    if (!in_array($mime, ['image/jpeg', 'image/png'])) {
                        $fail('File harus berupa gambar JPG atau PNG.');
                    }
                },
            ],
        ], [
            'payment_proof.required' => 'Bukti pembayaran wajib diupload.',
            'payment_proof.file'     => 'Upload harus berupa file.',
            'payment_proof.mimes'    => 'Format file harus JPG atau PNG.',
            'payment_proof.max'      => 'Ukuran file maksimal 2MB.',
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

        if ($order->payment_proof) {
            Storage::disk('public')->delete($order->payment_proof);
        }

        $path = $request->file('payment_proof')->store('payment_proofs', 'public');

        $order->update([
            'payment_proof' => $path,
            'proof_status'  => 'uploaded',
        ]);

        return response()->json(['data' => $order->load(['items.book', 'paymentMethod'])]);
    }
}
