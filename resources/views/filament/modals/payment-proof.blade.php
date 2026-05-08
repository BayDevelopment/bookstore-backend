<div class="p-5 space-y-4">

    <!-- Content -->
    @if (!empty($record->payment_proof))
        @php
            $file = $record->payment_proof;
            $url = asset('storage/' . $file);
        @endphp

        <div class="rounded-xl border border-gray-100 bg-gray-50 p-3">
            @if (Str::endsWith($file, ['.jpg', '.jpeg', '.png']))
                <img src="{{ $url }}" class="rounded-lg shadow-sm max-w-full mx-auto" />
            @else
                <div class="text-center py-6">
                    <p class="text-sm text-gray-500 mb-2">File bukan gambar</p>
                    <a href="{{ $url }}" target="_blank"
                        class="inline-flex items-center gap-2 text-blue-600 hover:underline text-sm font-medium">
                        📎 Lihat / Download File
                    </a>
                </div>
            @endif
        </div>
    @else
        <div class="text-center py-10">
            <p class="text-4xl mb-2">📭</p>
            <p class="text-sm text-gray-500">Belum ada bukti pembayaran</p>
        </div>
    @endif

    <!-- Catatan -->
    @if (!empty($record->proof_note))
        <div class="bg-yellow-50 border border-yellow-100 rounded-xl p-3">
            <p class="text-xs text-yellow-600 font-semibold mb-1">Catatan</p>
            <p class="text-sm text-gray-700">
                {{ $record->proof_note }}
            </p>
        </div>
    @endif
</div>
