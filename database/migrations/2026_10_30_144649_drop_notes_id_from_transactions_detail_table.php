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
       Schema::table('transactions_detail', function (Blueprint $table) {
            // Jika category_id berelasi (foreign key), lepas constraint-nya dulu:
            // $table->dropForeign(['category_id']);

            // Hapus kolomnya
            $table->dropColumn('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       Schema::table('transactions_detail', function (Blueprint $table) {
            // Kembalikan kolom jika suatu saat di-rollback
            $table->string('notes')->nullable()->after('subtotal');
        });
    }
};
