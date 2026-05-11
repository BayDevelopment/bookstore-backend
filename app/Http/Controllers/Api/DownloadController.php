<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BookModel;
use App\Models\OrderModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class DownloadController extends Controller
{
    /**
     * Generate signed URL untuk download PDF.
     * Route ini WAJIB pakai middleware auth:sanctum.
     */
    public function generateLink(Request $request, OrderModel $order, BookModel $book)
    {
        $user = $request->user();

        // 1. Cek ownership order
        if ($user->id !== $order->user_id) {
            return response()->json([
                'message' => 'Ups, Akses Ditolak! Kamu tidak memiliki akses ke pesanan ini.'
            ], 403);
        }

        // 2. Cek status order harus confirmed/completed
        if (!in_array($order->status, ['confirmed', 'completed'])) {
            return response()->json([
                'message' => 'Pesanan belum dikonfirmasi oleh admin.'
            ], 403);
        }

        // 3. Cek proof_status harus verified (kecuali cash)
        if (
            $order->paymentMethod?->code !== 'cash'
            && $order->proof_status !== 'verified'
        ) {
            return response()->json([
                'message' => 'Bukti pembayaran belum diverifikasi.'
            ], 403);
        }

        // 4. Cek item PDF ada di order ini
        $orderItem = $order->items()
            ->where('book_id', $book->id)
            ->where('type', 'pdf')
            ->first();

        if (!$orderItem) {
            return response()->json([
                'message' => 'Item PDF tidak ditemukan dalam pesanan ini.'
            ], 404);
        }

        // 5. Cek file fisik ada
        if (!$book->file_path || !Storage::disk('local')->exists($book->file_path)) {
            return response()->json([
                'message' => 'File PDF tidak tersedia. Hubungi admin.'
            ], 404);
        }

        // 6. Buat signed URL expire 5 menit, ikat ke user_id
        $url = URL::temporarySignedRoute(
            'download.pdf',
            now()->addMinutes(5),
            [
                'order' => $order->id,
                'book'  => $book->id,
                'uid'   => $user->id,   // ← kunci: ikat URL ke user ini
            ]
        );

        return response()->json(['url' => $url]);
    }

    public function download(Request $request, OrderModel $order, BookModel $book)
    {
        // 1. Validasi signature & expiry — otomatis dari middleware('signed'),
        //    tapi double-check di sini untuk pesan error yang lebih jelas.
        if (!$request->hasValidSignature()) {
            abort(403, 'Link tidak valid atau sudah kadaluarsa. Kembali ke halaman pesanan untuk generate link baru.');
        }

        // 2. Ambil uid dari query string (disisipkan saat generateLink)
        $uid = (int) $request->query('uid');
        if (!$uid) {
            abort(403, 'Ups, Akses Ditolak! Parameter tidak valid.');
        }

        // 3. Pastikan uid cocok dengan user_id pemilik order
        //    Ini mencegah User A share signed URL ke User B:
        //    meski URL valid & belum expire, uid A ≠ user_id order B → tolak
        if ($uid !== $order->user_id) {
            abort(403, 'Ups, Akses Ditolak! Link ini hanya bisa digunakan oleh pemilik pesanan.');
        }

        // 4. Verifikasi user dengan uid benar-benar ada di DB
        //    (defense in depth — cegah uid yang dimanipulasi meski signature valid)
        $user = User::find($uid);
        if (!$user) {
            abort(403, 'Ups, Akses Ditolak! Pengguna tidak ditemukan.');
        }

        // 5. Cek file fisik masih ada
        if (!$book->file_path || !Storage::disk('local')->exists($book->file_path)) {
            abort(404, 'File PDF tidak tersedia. Hubungi admin.');
        }

        $filename = preg_replace('/[^A-Za-z0-9\-_]/', '_', $book->title) . '.pdf';

        return Storage::disk('local')->download(
            $book->file_path,
            $filename,
            [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control'       => 'no-store, no-cache, must-revalidate',
                'Pragma'              => 'no-cache',
                'X-Robots-Tag'        => 'noindex',
            ]
        );
    }
}
