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
        Schema::table('transactions', function (Blueprint $table) {
            // Contoh 1: Tambah kolom string biasa
            $table->string('notes')->nullable()->after('date');
            
            // Contoh 2: Tambah foreign key / id kategori
            // $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Drop kolom jika migration di-rollback
            $table->dropColumn('notes');
            
            // Jika foreign key:
            // $table->dropConstrainedForeignId('category_id');
        });
    }
};
