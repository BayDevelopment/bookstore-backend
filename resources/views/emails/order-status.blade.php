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

        @if ($order->status === 'confirmed')
            <div class="header" style="background:#16a34a;">
                <h1 style="color:white; margin:0; font-size:20px;">📚 Bookstore</h1>
                <p style="color:#bbf7d0; margin:4px 0 0; font-size:14px;">Pesanan Diterima ✅</p>
            </div>
        @else
            <div class="header" style="background:#dc2626;">
                <h1 style="color:white; margin:0; font-size:20px;">📚 Bookstore</h1>
                <p style="color:#fecaca; margin:4px 0 0; font-size:14px;">Pesanan Ditolak ❌</p>
            </div>
        @endif

        <div class="body">
            <p>Halo <strong>{{ $order->user->name }}</strong>,</p>

            @if ($order->status === 'confirmed')
                <p>Kabar baik! Pesananmu telah <strong style="color:#16a34a;">diterima</strong> dan sedang diproses.</p>

                <div
                    style="background:#f0fdf4; border:1px solid #86efac; border-radius:8px; padding:16px; margin:16px 0; font-size:13px; color:#166534;">
                    ✅ Silakan ambil buku kamu di perpustakaan/akademik dengan menunjukkan <strong>Order
                        #{{ $order->id }}</strong>.
                </div>
            @elseif ($order->status === 'rejected')
                <p>Mohon maaf, pesananmu <strong style="color:#dc2626;">ditolak</strong> oleh admin.</p>

                @if ($order->proof_note)
                    <div
                        style="background:#fef2f2; border:1px solid #fca5a5; border-radius:8px; padding:16px; margin:16px 0;">
                        <p style="font-size:12px; color:#6b7280; margin:0 0 6px;">Catatan dari admin:</p>
                        <p style="font-size:14px; color:#991b1b; margin:0; font-weight:500;">{{ $order->proof_note }}
                        </p>
                    </div>
                @endif

                <p style="font-size:13px; color:#6b7280;">Jika ada pertanyaan, silakan hubungi kami.</p>
            @endif

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

                <div class="row-total" style="color: {{ $order->status === 'confirmed' ? '#16a34a' : '#2563eb' }};">
                    <span>Total Pembayaran</span>
                    <span>Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="row" style="border:none; padding: 4px 0;">
                <span class="label" style="color:#6b7280; font-size:13px;">Tanggal Pesan</span>
                <span class="value" style="font-size:13px;">{{ $order->created_at->format('d M Y, H:i') }}</span>
            </div>
        </div>

        <div class="footer">
            © {{ date('Y') }} Bookstore. Email ini dikirim otomatis, jangan dibalas.
        </div>
    </div>
</body>

</html>
