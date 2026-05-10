<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BookModel;
use App\Models\OrderModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\PersonalAccessToken;

class DownloadController extends Controller
{
    public function download(Request $request, OrderModel $order, BookModel $book)
    {
        // ✅ Ambil token dari query param
        $token = $request->query('token');
        if ($token) {
            $request->headers->set('Authorization', 'Bearer ' . $token);
        }

        $user = auth('sanctum')->user();

        if (!$user) {
            abort(403, 'Silakan login terlebih dahulu.');
        }

        // ✅ Harus pemilik order
        if ($user->id !== $order->user_id) {
            abort(403, 'Akses ditolak.');
        }

        // ✅ Status order
        if (!in_array($order->status, ['verified', 'completed', 'confirmed'])) {
            abort(403, 'Pesanan belum dikonfirmasi.');
        }

        // ✅ Proof harus verified (kecuali cash)
        if (
            $order->paymentMethod?->code !== 'cash'
            && $order->proof_status !== 'verified'
        ) {
            abort(403, 'Bukti pembayaran belum diverifikasi.');
        }

        // ✅ Item PDF harus ada di order ini
        $orderItem = $order->items()
            ->where('book_id', $book->id)
            ->where('type', 'pdf')
            ->first();

        if (!$orderItem) {
            abort(404, 'Item tidak ditemukan dalam pesanan ini.');
        }

        // ✅ File harus ada
        if (!$book->file_path || !Storage::disk('local')->exists($book->file_path)) {
            abort(404, 'File PDF tidak tersedia.');
        }

        // ✅ Stream download
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
