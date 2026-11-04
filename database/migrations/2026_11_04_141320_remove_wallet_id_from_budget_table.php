<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('budget', function (Blueprint $table) {
            // Drop constraint + kolom
            $table->dropConstrainedForeignId('wallet_id');
        });
    }

    public function down(): void
    {
        Schema::table('budget', function (Blueprint $table) {
            // Jika di-rollback, buat kembali kolom beserta constraint-nya
            $table->foreignId('wallet_id')
                  ->constrained('wallet', 'wallet_id')
                  ->cascadeOnDelete();
        });
    }
};