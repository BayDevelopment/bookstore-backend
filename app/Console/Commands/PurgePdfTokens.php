<?php

namespace App\Console\Commands;

use App\Models\PdfAccessTokenModel;
use Illuminate\Console\Command;

class PurgePdfTokens extends Command
{
    protected $signature   = 'pdf:purge-tokens';
    protected $description = 'Hapus PDF access token yang sudah kedaluwarsa';

    public function handle(): int
    {
        $deleted = PdfAccessTokenModel::purgeExpired();
        $this->info("✅ {$deleted} token expired berhasil dihapus.");
        return 0;
    }
}
