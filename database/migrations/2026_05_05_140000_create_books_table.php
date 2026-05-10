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
        Schema::create('books', function (Blueprint $table) {
            $table->id();

            $table->string('title')->index();
            $table->string('author')->index();

            $table->foreignId('category_id')
                ->constrained('categories')
                ->cascadeOnDelete();

            // FIXED: nullable() harus sebelum constrained()
            $table->foreignId('fakultas_id')
                ->nullable()
                ->constrained('fakultas')
                ->nullOnDelete();

            $table->string('cover')->nullable();
            $table->text('description')->nullable();

            // Cetak
            $table->boolean('has_print')->default(false);
            $table->decimal('price_print', 10, 2)->nullable();
            $table->integer('stock')->default(0);

            // Digital
            $table->boolean('has_pdf')->default(false);
            $table->decimal('price_pdf', 10, 2)->nullable();

            $table->string('file_path')->nullable();

            $table->timestamps();

            // Composite index: filter buku fisik + stok
            $table->index(['has_print', 'stock']);

            // Composite index: filter ebook
            $table->index(['has_pdf']);

            // Composite index: filter by category + fakultas (query umum)
            $table->index(['category_id', 'fakultas_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
