<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DownloadController;
use App\Http\Controllers\Api\FakultasController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentMethodController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\PdfController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ── PUBLIC ───────────────────────────────────────────────────────────────────
Route::post('/register',     [AuthController::class, 'register']);
Route::post('/login',        [AuthController::class, 'login']);
Route::post('/email/resend', [AuthController::class, 'resendVerification']);

// routes/api.php
Route::get('/books', [BookController::class, 'index']);
Route::get('/books/{id}', [BookController::class, 'show']);
Route::post('/books/{id}', [BookController::class, 'incrementViewCount']);

Route::get('/categories',      [CategoryController::class, 'index'])->middleware('throttle:60,1');
Route::get('/fakultas',        [FakultasController::class, 'fakultas']);
Route::get('/prodi',           [FakultasController::class, 'prodi']);
Route::get('/payment-methods', [PaymentMethodController::class, 'index']);

Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password',  [AuthController::class, 'resetPassword']);

// ── VERIFIKASI EMAIL ─────────────────────────────────────────────────────────
Route::get('/email/verify/{id}/{hash}', function (Request $request, $id, $hash) {
    $user = User::findOrFail($id);
    $frontendUrl = config('app.frontend_url', 'http://localhost:5173');

    if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
        return redirect($frontendUrl . '/login?verified=0');
    }

    if ($user->hasVerifiedEmail()) {
        return redirect($frontendUrl . '/login?verified=1');
    }

    $user->markEmailAsVerified();

    return redirect($frontendUrl . '/login?verified=1');
})->middleware('signed')->name('verification.verify');


Route::get('/orders/{order}/download/{book}', [DownloadController::class, 'download'])
    ->middleware('signed')
    ->name('download.pdf');


// ── PROTECTED (auth:sanctum) ─────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me',      [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Cart
    Route::get('/cart/count',   [CartController::class, 'count']);
    Route::get('/cart',         [CartController::class, 'index']);
    Route::post('/cart',        [CartController::class, 'store']);
    Route::patch('/cart/{id}',  [CartController::class, 'update']);
    Route::delete('/cart/{id}', [CartController::class, 'destroy']);

    // Orders
    Route::get('/orders',  [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);

    Route::middleware('order.owner')->group(function () {
        Route::get('/orders/{id}',          [OrderController::class, 'show']);
        Route::post('/orders/{id}/payment', [OrderController::class, 'uploadPayment']);
    });

    // Generate signed download URL — butuh login + ownership dicek di controller
    Route::get('/orders/{order}/download-link/{book}', [DownloadController::class, 'generateLink']);
    Route::post(
        '/orders/{orderId}/pdf-token/{bookId}',
        [PdfController::class, 'issueToken']
    );

    // Profile
    Route::get('/profile',             [ProfileController::class, 'show']);
    Route::put('/profile',             [ProfileController::class, 'update']);
    Route::put('/profile/password',    [ProfileController::class, 'updatePassword']);
    Route::post('/profile/logout-all', [ProfileController::class, 'logoutAll']);
});
