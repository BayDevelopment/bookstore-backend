<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\LikeController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;
use App\Models\User;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// =====================
// PUBLIC
// =====================
Route::post('/register',  [AuthController::class, 'register']);
Route::post('/login',     [AuthController::class, 'login']);
Route::post('/email/resend', [AuthController::class, 'resendVerification']); // ← public
Route::get('/books',      [BookController::class, 'index']);
Route::get('/books/{id}', [BookController::class, 'show']);

// =====================
// VERIFIKASI EMAIL
// =====================
Route::get('/email/verify/{id}/{hash}', function (Request $request, $id, $hash) {

    // Cari user berdasarkan ID
    $user = User::findOrFail($id);

    // Cek signature URL valid
    if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
        return redirect(env('FRONTEND_URL', 'http://localhost:5173') . '/login?verified=0');
    }

    // Cek apakah sudah diverifikasi
    if ($user->hasVerifiedEmail()) {
        return redirect(env('FRONTEND_URL', 'http://localhost:5173') . '/login?verified=1');
    }

    // Tandai email sudah diverifikasi
    $user->markEmailAsVerified();

    return redirect(env('FRONTEND_URL', 'http://localhost:5173') . '/login?verified=1');
})->middleware(['signed'])->name('verification.verify');

// =====================
// PROTECTED
// =====================
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);

    // Like
    Route::get('/likes',         [LikeController::class, 'index']);
    Route::post('/likes/{book}', [LikeController::class, 'toggle']);

    // Cart
    Route::get('/cart',         [CartController::class, 'index']);
    Route::post('/cart',        [CartController::class, 'store']);
    Route::delete('/cart/{id}', [CartController::class, 'destroy']);

    // Order
    Route::get('/orders',             [OrderController::class, 'index']);
    Route::post('/orders',            [OrderController::class, 'store']);
    Route::post('/orders/{id}/proof', [OrderController::class, 'uploadProof']);
});
