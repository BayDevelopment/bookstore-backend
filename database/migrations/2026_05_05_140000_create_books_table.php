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
            $table->string('title');
            $table->string('author');
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->foreignId('fakultas_id')->nullable()->constrained('fakultas')->nullOnDelete(); // ← hapus ->after()
            $table->enum('type', ['digital', 'cetak']);
            $table->decimal('price', 10, 2);
            $table->string('cover')->nullable();
            $table->string('file_path')->nullable();
            $table->integer('stock')->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
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
