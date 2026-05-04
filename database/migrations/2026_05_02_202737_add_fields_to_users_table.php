<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nim')->nullable()->unique();
            $table->string('fakultas')->nullable();
            $table->string('prodi')->nullable(); // ← tambahan
            $table->enum('role', ['guest', 'customer', 'admin'])->default('customer');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nim', 'fakultas', 'prodi', 'role']);
        });
    }
};
