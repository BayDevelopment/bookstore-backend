<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BookModel;
use App\Models\CartModel;
use App\Models\OrderModel;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $totalBooks   = BookModel::where('stock', '>', 0)->count();
        $totalCart    = CartModel::where('user_id', $user->id)->sum('qty');
        $totalOrders  = OrderModel::where('user_id', $user->id)->count();

        $recentOrders = OrderModel::with(['items.book'])
            ->where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get()
            ->map(fn($order) => [
                'id'         => $order->id,
                'status'     => $order->status,
                'proof_status' => $order->proof_status,
                'total'      => $order->total,
                'created_at' => $order->created_at,
                'book_title' => $order->items?->first()?->book?->title ?? '-',
                'book_cover' => $order->items?->first()?->book?->cover,
            ]);

        return response()->json([
            'data' => [
                'total_books'   => $totalBooks,
                'total_cart'    => $totalCart,
                'total_orders'  => $totalOrders,
                'recent_orders' => $recentOrders,
            ]
        ]);
    }
}
