<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('filament.admin.pages.dashboard');
});

// Hanya GET saja, tanpa POST!
Route::get('/login', function () {
    return redirect(env('FRONTEND_URL', 'http://localhost:5173') . '/login');
})->name('login');
