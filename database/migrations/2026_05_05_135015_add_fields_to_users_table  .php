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
        Schema::table('users', function (Blueprint $table) {
            // 1. Tambah kolom dulu
            $table->unsignedBigInteger('fakultas_id')->nullable()->after('password');
            $table->unsignedBigInteger('prodi_id')->nullable()->after('fakultas_id');

            // 2. Baru tambah foreign key
            $table->foreign('fakultas_id')->references('id')->on('fakultas')->nullOnDelete();
            $table->foreign('prodi_id')->references('id')->on('prodi')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['fakultas_id']);
            $table->dropForeign(['prodi_id']);
            $table->dropColumn(['fakultas_id', 'prodi_id']);
        });
    }
};
