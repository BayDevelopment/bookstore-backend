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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->decimal('total', 10, 2);
            $table->enum('status', ['pending', 'confirmed', 'rejected'])->default('pending');
            $table->string('payment_proof')->nullable();
            $table->enum('proof_status', [
                'not_uploaded',   // belum upload bukti
                'uploaded',       // sudah upload, menunggu verifikasi admin
                'verified',       // bukti valid, admin konfirmasi
                'invalid'         // bukti ditolak admin (blur, salah, dll)
            ])->default('not_uploaded');
            $table->text('proof_note')->nullable(); // catatan admin jika ditolak
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
