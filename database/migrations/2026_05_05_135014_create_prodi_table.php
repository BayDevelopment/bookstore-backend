<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('prodi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fakultas_id')
                ->constrained('fakultas')
                ->onDelete('cascade'); // kalau fakultas dihapus → prodi ikut hilang

            $table->string('kode_prodi')->unique(); // contoh: TI, SI
            $table->string('nama_prodi'); // contoh: Teknik Informatika
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prodi');
    }
};
