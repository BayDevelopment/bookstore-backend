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
            $table->string('category');
            $table->string('fakultas')->nullable();
            $table->enum('type', ['digital', 'cetak']);
            $table->decimal('price', 10, 2);
            $table->string('cover')->nullable();
            $table->string('file_path')->nullable(); // khusus digital
            $table->integer('stock')->default(0);   // khusus cetak
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
