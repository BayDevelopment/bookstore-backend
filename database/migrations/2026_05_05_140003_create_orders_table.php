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
            $table->foreignId('payment_method_id')
                ->constrained('payment_methods')
                ->restrictOnDelete();
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->decimal('total', 10, 2);
            $table->enum('status', ['pending', 'confirmed', 'rejected'])->default('pending');
            $table->string('payment_proof')->nullable();
            $table->enum('proof_status', [
                'not_uploaded',
                'uploaded',
                'verified',
                'invalid'
            ])->default('not_uploaded');
            $table->text('proof_note')->nullable();
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
