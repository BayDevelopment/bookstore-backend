<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CategoriesModel;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = CategoriesModel::where('is_active', true)
            ->select('id', 'name', 'icon')
            ->get();

        return response()->json($categories);
    }
}
