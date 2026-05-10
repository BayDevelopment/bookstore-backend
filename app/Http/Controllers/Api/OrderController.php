<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Mail\NewOrderAdminMail;
use App\Mail\OrderConfirmationMail;
use App\Models\BookModel;
use App\Models\CartModel;
use App\Models\OrderItemModel;
use App\Models\OrderModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = OrderModel::with(['items.book', 'paymentMethod'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'data' => OrderResource::collection($orders)
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'items'             => 'required|array|min:1',
            'items.*.book_id'   => 'required|exists:books,id',
            'items.*.quantity'  => 'required|integer|min:1|max:10',
            'items.*.type'      => 'required|in:print,pdf',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'from_cart'         => 'boolean',
            'items.*.cart_id'   => 'nullable|exists:carts,id',
        ]);

        $order = null;

        try {
            DB::transaction(function () use ($validated, $request, &$order) {
                $total = 0;
                $books = [];

                foreach ($validated['items'] as $item) {
                    $book = BookModel::lockForUpdate()->findOrFail($item['book_id']);

                    if ($item['type'] === 'print') {
                        if (!$book->has_print) {
                            throw new \RuntimeException("Buku \"{$book->title}\" tidak tersedia dalam versi cetak.");
                        }
                        if ($book->stock < $item['quantity']) {
                            throw new \RuntimeException("Stok buku \"{$book->title}\" tidak mencukupi.");
                        }
                        $price = (float) $book->price_print;
                    } else {
                        if (!$book->has_pdf) {
                            throw new \RuntimeException("Buku \"{$book->title}\" tidak tersedia dalam versi PDF.");
                        }
                        $item['quantity'] = 1;
                        $price = (float) $book->price_pdf;
                    }

                    $total += $price * $item['quantity'];
                    $books[] = [
                        'book'     => $book,
                        'quantity' => $item['quantity'],
                        'type'     => $item['type'],
                        'price'    => $price,
                        'cart_id'  => $item['cart_id'] ?? null,
                    ];
                }

                $order = OrderModel::create([
                    'user_id'           => $request->user()->id,
                    'payment_method_id' => $validated['payment_method_id'],
                    'total'             => $total,
                    'status'            => 'pending',
                ]);

                $cartIdsToDelete = [];

                foreach ($books as $entry) {
                    OrderItemModel::create([
                        'order_id' => $order->id,
                        'book_id'  => $entry['book']->id,
                        'qty'      => $entry['quantity'],
                        'type'     => $entry['type'],
                        'price'    => $entry['price'],
                    ]);

                    if ($entry['type'] === 'print') {
                        $entry['book']->decrement('stock', $entry['quantity']);
                    }

                    if ($entry['cart_id']) {
                        $cartIdsToDelete[] = $entry['cart_id'];
                    }
                }

                if (!empty($validated['from_cart']) && !empty($cartIdsToDelete)) {
                    CartModel::where('user_id', $request->user()->id)
                        ->whereIn('id', $cartIdsToDelete)
                        ->delete();
                }
            });

            // Ambil semua user dengan role admin
            $admins = User::where('role', 'admin')->get();

            Mail::to($request->user()->email)
                ->queue(new OrderConfirmationMail(
                    $order->load(['items.book', 'paymentMethod', 'user'])
                ));

            // ✅ Kirim ke semua admin
            foreach ($admins as $admin) {
                Mail::to($admin->email)
                    ->queue(new NewOrderAdminMail($order));
            }

            return response()->json([
                'data' => new OrderResource($order->load(['items.book', 'paymentMethod']))
            ], 201);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['message' => 'Terjadi kesalahan server, coba lagi.'], 500);
        }
    }

    public function show(Request $request, $id)
    {
        // ✅ Ambil dari attributes yang di-set middleware (sudah divalidasi ownership-nya)
        // Load relasi yang belum di-load oleh middleware
        $order = $request->attributes->get('order')
            ->load(['items.book', 'paymentMethod', 'user']);

        return response()->json([
            'data' => new OrderResource($order)
        ]);
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

        // ✅ Ambil dari attributes middleware, sudah tervalidasi ownership
        $order = $request->attributes->get('order');

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

        return response()->json([
            'data' => new OrderResource($order->load(['items.book', 'paymentMethod']))
        ]);
    }
}
