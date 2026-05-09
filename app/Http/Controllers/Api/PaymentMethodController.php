<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\JsonResponse;

class PaymentMethodController extends Controller
{
    public function index(): JsonResponse
    {
        $methods = PaymentMethod::where('is_active', true)
            ->select('id', 'name', 'code', 'description', 'bank_name', 'account_name', 'account_number')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json(['data' => $methods]);
    }
}
