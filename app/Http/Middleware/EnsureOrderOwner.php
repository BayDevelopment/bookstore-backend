<?php

namespace App\Http\Middleware;

use App\Models\OrderModel;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureOrderOwner
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // ✅ FIX: route pakai {id}, bukan {order}
        $id = $request->route('id');

        if (!$id) {
            return response()->json(['message' => 'Order tidak ditemukan'], 404);
        }

        $order = OrderModel::find($id);

        if (!$order) {
            return response()->json(['message' => 'Order tidak ditemukan'], 404);
        }

        if (Auth::id() !== $order->user_id) {
            return response()->json(['message' => 'Forbidden: Bukan pemilik order'], 403);
        }

        // ✅ Attach order ke request supaya controller tidak perlu query ulang
        $request->attributes->set('order', $order);

        return $next($request);
    }
}
