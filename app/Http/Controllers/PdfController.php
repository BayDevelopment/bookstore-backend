<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\BookModel;
use App\Models\OrderItemModel;
use App\Models\OrderModel;
use App\Models\PdfAccessTokenModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PdfController extends Controller
{
    public function issueToken(Request $request, int $orderId, int $bookId): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();

        // ── 1. Cari order milik user ──────────────────────────────────────
        $order = OrderModel::where('id', $orderId)
            ->where('user_id', $user->id)
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Pesanan tidak ditemukan.'], 404);
        }

        // ── 2. Order harus sudah verified ─────────────────────────────────
        if ($order->proof_status !== 'verified') {
            return response()->json([
                'message' => 'Pembayaran belum diverifikasi. PDF belum bisa diakses.',
            ], 403);
        }

        // ── 3. Cek item PDF ada di order ini ──────────────────────────────
        $item = OrderItemModel::where('order_id', $order->id)
            ->where('book_id', $bookId)
            ->where('type', 'pdf')
            ->first();

        if (!$item) {
            return response()->json([
                'message' => 'Buku PDF ini tidak ada dalam pesananmu.',
            ], 403);
        }

        // ── 4. Cek file PDF benar-benar ada di storage ───────────────────
        $book = BookModel::find($bookId);
        if (!$book || !$book->file_path || !Storage::disk('private')->exists($book->file_path)) {
            return response()->json([
                'message' => 'File PDF tidak tersedia. Hubungi admin.',
            ], 404);
        }

        // ── 5. Hapus token lama user untuk buku ini (bersih) ─────────────
        PdfAccessTokenModel::where('user_id', $user->id)
            ->where('book_id', $bookId)
            ->delete();

        // ── 6. Buat token baru, TTL 5 menit ──────────────────────────────
        $token = PdfAccessTokenModel::create([
            'token'      => Str::random(64),
            'user_id'    => $user->id,
            'order_id'   => $order->id,
            'book_id'    => $bookId,
            'expires_at' => now()->addMinutes(5),
            'used'       => false,
        ]);

        return response()->json([
            'token'      => $token->token,
            'expires_at' => $token->expires_at->toIso8601String(),
            'book_title' => $book->title,
        ]);
    }

    // ════════════════════════════════════════════════════════════════════════
    // STEP 2 — Browser/iframe minta PDF pakai token
    // Route: GET /pdf/view?token=xxx
    // ════════════════════════════════════════════════════════════════════════

    public function viewPdf(Request $request): StreamedResponse|\Illuminate\Http\JsonResponse
    {
        $tokenStr = $request->query('token');

        if (!$tokenStr) {
            abort(403, 'Token tidak diberikan.');
        }

        // ── 1. Cari & validasi token ──────────────────────────────────────
        $tokenRecord = PdfAccessTokenModel::where('token', $tokenStr)
            ->with('book')
            ->first();

        if (!$tokenRecord) {
            abort(403, 'Token tidak valid.');
        }

        if (!$tokenRecord->isValid()) {
            $tokenRecord->delete();
            abort(403, 'Token sudah kedaluwarsa atau telah digunakan. Minta link baru.');
        }

        // ── 3. Cek file masih ada ─────────────────────────────────────────
        $book    = $tokenRecord->book;
        $pdfPath = $book->file_path; // ← sudah diperbaiki

        if (!$pdfPath || !Storage::disk('private')->exists($pdfPath)) {
            abort(404, 'File PDF tidak ditemukan.');
        }

        // ── 4. (Opsional) Tandai token sebagai "used" untuk single-use ───
        //      Nonaktifkan baris ini kalau mau PDF bisa di-refresh dalam 5 menit.
        // $tokenRecord->markUsed();

        // ── 5. Stream PDF dengan header yang mencegah download ────────────
        $fileSize = Storage::disk('private')->size($pdfPath);

        return response()->stream(function () use ($pdfPath) {
            ob_clean(); // ← tambah ini
            flush();
            $stream = Storage::disk('private')->readStream($pdfPath);
            fpassthru($stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
        }, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Length'      => $fileSize,
            'Content-Disposition' => 'inline; filename="document.pdf"',
            'X-Frame-Options'     => 'ALLOWALL',
            'Content-Security-Policy' => "frame-ancestors 'self' " . env('FRONTEND_URL', 'http://localhost:5173'),
            'Cache-Control'       => 'no-store, no-cache, must-revalidate, private',
            'Pragma'              => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
