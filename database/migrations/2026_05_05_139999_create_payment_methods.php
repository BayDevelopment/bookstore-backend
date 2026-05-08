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
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name');                       // Transfer Bank, Bayar di Tempat
            $table->string('code')->unique();             // transfer, cash
            $table->string('description')->nullable();
            $table->string('midtrans_payment_type')       // bank_transfer, gopay, qris, dll
                ->nullable();                           // null = cash/manual
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
