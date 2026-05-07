<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CategoriesModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;


class CategoryController extends Controller
{

    public function index()
    {
        $categories = CategoriesModel::where('is_active', true)
            ->select('id', 'name', 'icon') // ⬅️ hanya ambil yang dibutuhkan
            ->get();

        return response()->json($categories);
    }
}
