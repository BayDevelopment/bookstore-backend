<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartModel;
use Illuminate\Http\Request;

class CartController extends Controller
{
    // Ambil jumlah item cart milik user yang login
    public function count(Request $request)
    {
        $count = CartModel::where('user_id', $request->user()->id)->count();

        return response()->json(['count' => $count]);
    }

    // Ambil list cart lengkap (opsional)
    public function index(Request $request)
    {
        $cart = CartModel::where('user_id', $request->user()->id)
            ->with('book') // relasi ke BookModel jika ada
            ->get();

        return response()->json(['data' => $cart]);
    }
}
