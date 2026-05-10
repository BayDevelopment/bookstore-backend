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
            background: #1e40af;
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
            color: #6b7280;
            flex: 1;
        }

        .row .value {
            color: #111827;
            font-weight: 600;
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
            color: #1e40af;
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
        }

        .badge-yellow {
            background: #fef9c3;
            color: #854d0e;
        }

        .badge-blue {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-purple {
            background: #f3e8ff;
            color: #6b21a8;
        }

        .alert {
            border-radius: 8px;
            padding: 14px 16px;
            font-size: 13px;
            margin: 16px 0;
        }

        .footer {
            text-align: center;
            padding: 16px 32px;
            color: #9ca3af;
            font-size: 12px;
            border-top: 1px solid #f0f0f0;
        }

        p {
            font-size: 14px;
            color: #374151;
            line-height: 1.6;
            margin: 0 0 12px;
        }

        a {
            color: #2563eb;
        }
    </style>
</head>

<body>
    <div class="card">

        <!-- HEADER -->
        <div class="header">
            <h1 style="color:white; margin:0; font-size:20px;">📚 Bookstore Admin</h1>
            <p style="color:#bfdbfe; margin:6px 0 0; font-size:14px;">Ada pesanan baru masuk!</p>
        </div>

        <div class="body">

            <!-- ALERT UTAMA -->
            <div class="alert" style="background:#eff6ff; border:1px solid #bfdbfe;">
                <p style="margin:0; color:#1e40af; font-weight:600; font-size:14px;">
                    🛒 Pesanan baru dari <strong>{{ $order->user->name }}</strong> telah masuk dan menunggu tindakan
                    admin.
                </p>
            </div>

            <!-- INFO CUSTOMER -->
            <div style="background:#f8fafc; border-radius:8px; padding:16px; margin:16px 0;">
                <div
                    style="font-size:11px; color:#6b7280; font-weight:700; letter-spacing:0.08em; margin-bottom:12px; text-transform:uppercase;">
                    Informasi Customer
                </div>
                <div class="row">
                    <span class="label">Nama</span>
                    <span class="value">{{ $order->user->name }}</span>
                </div>
                <div class="row">
                    <span class="label">Email</span>
                    <span class="value">
                        <a href="mailto:{{ $order->user->email }}">{{ $order->user->email }}</a>
                    </span>
                </div>
                <div class="row" style="border:none;">
                    <span class="label">Tanggal Pesan</span>
                    <span class="value">{{ $order->created_at->format('d M Y, H:i') }} WIB</span>
                </div>
            </div>

            <!-- DETAIL PESANAN -->
            <div style="background:#f8fafc; border-radius:8px; padding:16px; margin:16px 0;">
                <div
                    style="font-size:11px; color:#6b7280; font-weight:700; letter-spacing:0.08em; margin-bottom:12px; text-transform:uppercase;">
                    Detail Pesanan — ORDER #{{ $order->id }}
                </div>

                @foreach ($order->items as $item)
                    <div class="row">
                        <span class="label" style="display:flex; align-items:center; gap:6px;">
                            {{ $item->book->title }}
                            <span style="color:#9ca3af; font-size:12px;">× {{ $item->qty }}</span>
                            <span class="badge {{ $item->type === 'print' ? 'badge-yellow' : 'badge-purple' }}">
                                {{ $item->type === 'print' ? '📚 Cetak' : '📄 PDF' }}
                            </span>
                        </span>
                        <span class="value">Rp {{ number_format($item->price * $item->qty, 0, ',', '.') }}</span>
                    </div>
                @endforeach

                <div class="row-total">
                    <span>Total Pembayaran</span>
                    <span>Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- METODE PEMBAYARAN -->
            <div class="row" style="border:none; padding: 4px 0 8px;">
                <span class="label" style="font-size:13px;">Metode Pembayaran</span>
                <span class="value">
                    <span class="badge badge-blue">
                        {{ $order->paymentMethod->code === 'cash' ? '💵' : '🏦' }}
                        {{ $order->paymentMethod->name }}
                    </span>
                </span>
            </div>

            <!-- STATUS PEMBAYARAN -->
            @if ($order->paymentMethod->code !== 'cash')
                <div class="alert" style="background:#fffbeb; border:1px solid #fcd34d; color:#92400e;">
                    ⏳ <strong>Menunggu bukti transfer</strong> — Customer belum mengupload bukti pembayaran.
                    Harap pantau dan verifikasi setelah bukti dikirim.
                </div>
            @else
                <div class="alert" style="background:#f0fdf4; border:1px solid #86efac; color:#166534;">
                    💵 <strong>Pembayaran Tunai</strong> — Customer akan membayar langsung saat pengambilan buku.
                </div>
            @endif

        </div>

        <div class="footer">
            Email ini dikirim otomatis oleh sistem Bookstore.<br>
            © {{ date('Y') }} Bookstore Admin Panel.
        </div>
    </div>
</body>

</html>
