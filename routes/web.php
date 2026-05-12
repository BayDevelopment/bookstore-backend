<?php

use App\Http\Controllers\PdfController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('filament.admin.pages.dashboard');
});

Route::get('/login', function () {
    return redirect(env('FRONTEND_URL', 'http://localhost:5173') . '/login');
})->name('login');

Route::get('/reset-password/{token}', function (Request $request, $token) {
    $email = $request->query('email', '');

    return redirect(
        env('FRONTEND_URL', 'http://localhost:5173')
            . '/reset-password?token=' . $token
            . '&email=' . urlencode($email)  // ✅ tambah email
    );
})->name('password.reset');

Route::get('/pdf/view', [PdfController::class, 'viewPdf'])
    ->name('pdf.view')
    ->middleware('throttle:30,1');
