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
        Schema::create('transactions_detail', function (Blueprint $table) {
            $table->id('transaction_detail_id');

            // Foreign key mengarah ke transaction_id pada tabel transactions
            $table->foreignId('transaction_id')
                  ->constrained('transactions', 'transaction_id')
                  ->cascadeOnDelete();

            // Field item detail transaksi
            $table->string('item_name');
            $table->integer('quantity')->default(1);
            $table->decimal('price', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->text('notes');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions_detail');
    }
};
