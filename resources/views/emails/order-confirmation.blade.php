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
            padding: 24px 32px;
            text-align: center;
        }

        .body {
            padding: 24px 32px;
        }

        .row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
            font-size: 14px;
        }

        .row .label {
            color: #374151;
            flex: 1;
        }

        .row .value {
            color: #111827;
            font-weight: 500;
            text-align: right;
            min-width: 120px;
        }

        .row-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            font-weight: bold;
            font-size: 15px;
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

        p {
            font-size: 14px;
            color: #374151;
            line-height: 1.6;
        }
    </style>
</head>

<body>
    <div class="card">
        <div class="header" style="background: #2563eb;">
            <h1 style="color:white; margin:0; font-size:20px;">📚 Bookstore</h1>
            <p style="color:#bfdbfe; margin:4px 0 0; font-size:14px;">Konfirmasi Pesanan</p>
        </div>

        <div class="body">
            <p>Halo <strong>{{ $order->user->name }}</strong>,</p>
            <p>Pesananmu berhasil dibuat! Berikut detailnya:</p>

            <div style="background:#f8fafc; border-radius:8px; padding:16px; margin:16px 0;">
                <div style="font-size:12px; color:#6b7280; margin-bottom:12px; font-weight:600; letter-spacing:0.05em;">
                    ORDER #{{ $order->id }}
                </div>

                @foreach ($order->items as $item)
                    <div class="row">
                        <span class="label">
                            {{ $item->book->title }}
                            <span style="color:#6b7280; font-size:12px;">× {{ $item->qty }}</span>
                        </span>
                        <span class="value">Rp {{ number_format($item->price * $item->qty, 0, ',', '.') }}</span>
                    </div>
                @endforeach

                <div class="row-total">
                    <span>Total Pembayaran</span>
                    <span>Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="row" style="border:none; padding: 4px 0 12px;">
                <span class="label" style="color:#6b7280; font-size:13px;">Metode Pembayaran</span>
                <span class="value"><span class="badge">{{ $order->paymentMethod->name }}</span></span>
            </div>

            <div class="row" style="border:none; padding: 4px 0 12px;">
                <span class="label" style="color:#6b7280; font-size:13px;">Tanggal Pesan</span>
                <span class="value" style="font-size:13px;">{{ $order->created_at->format('d M Y, H:i') }}</span>
            </div>

            @if ($order->paymentMethod->code !== 'cash')
                <div
                    style="background:#fffbeb; border:1px solid #fcd34d; border-radius:8px; padding:12px; font-size:13px; color:#92400e; margin-top:16px;">
                    ⚠️ Silakan upload bukti pembayaran di halaman <strong>Pesanan Saya</strong> agar pesanan segera
                    diproses.
                </div>
            @else
                <div
                    style="background:#f0fdf4; border:1px solid #86efac; border-radius:8px; padding:12px; font-size:13px; color:#166534; margin-top:16px;">
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
