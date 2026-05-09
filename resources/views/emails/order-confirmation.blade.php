<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        .card {
            background: white;
            max-width: 560px;
            margin: 0 auto;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .header {
            background: #2563eb;
            padding: 24px 32px;
            text-align: center;
        }

        .header h1 {
            color: white;
            margin: 0;
            font-size: 20px;
        }

        .body {
            padding: 24px 32px;
        }

        .item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
            font-size: 14px;
        }

        .total {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            font-weight: bold;
            font-size: 16px;
            color: #2563eb;
        }

        .badge {
            display: inline-block;
            background: #eff6ff;
            color: #2563eb;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
        }

        .footer {
            text-align: center;
            padding: 16px 32px;
            color: #9ca3af;
            font-size: 12px;
        }
    </style>
</head>

<body>
    <div class="card">
        <div class="header">
            <h1>📚 Bookstore</h1>
            <p style="color:#bfdbfe; margin:4px 0 0; font-size:14px">Konfirmasi Pesanan</p>
        </div>
        <div class="body">
            <p>Halo <strong>{{ $order->user->name }}</strong>,</p>
            <p>Pesananmu berhasil dibuat! Berikut detailnya:</p>

            <div style="background:#f8fafc; border-radius:8px; padding:16px; margin:16px 0;">
                <div style="font-size:12px; color:#6b7280; margin-bottom:8px;">ORDER #{{ $order->id }}</div>

                @foreach ($order->items as $item)
                    <div class="item">
                        <span>{{ $item->book->title }} × {{ $item->qty }}</span>
                        <span>Rp {{ number_format($item->price * $item->qty, 0, ',', '.') }}</span>
                    </div>
                @endforeach

                <div class="total">
                    <span>Total</span>
                    <span>Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                </div>
            </div>

            <div style="margin-bottom:12px;">
                <span style="font-size:13px; color:#6b7280;">Metode Pembayaran: </span>
                <span class="badge">{{ $order->paymentMethod->name }}</span>
            </div>

            @if ($order->paymentMethod->code !== 'cash')
                <div
                    style="background:#fffbeb; border:1px solid #fcd34d; border-radius:8px; padding:12px; font-size:13px; color:#92400e;">
                    ⚠️ Silakan upload bukti pembayaran di halaman <strong>Pesanan Saya</strong> agar pesanan segera
                    diproses.
                </div>
            @else
                <div
                    style="background:#f0fdf4; border:1px solid #86efac; border-radius:8px; padding:12px; font-size:13px; color:#166534;">
                    ✅ Pembayaran dilakukan di tempat saat pengambilan buku.
                </div>
            @endif
        </div>
        <div class="footer">
            © {{ date('Y') }} Bookstore. Email ini dikirim otomatis, jangan dibalas.
        </div>
    </div>
</body>

</html>
