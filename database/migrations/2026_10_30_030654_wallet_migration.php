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
        Schema::create('wallet', function (Blueprint $table) {
            $table->id('wallet_id');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('transaction_id')
                ->constrained('transactions', 'transaction_id')
                ->cascadeOnDelete();
            $table->decimal('money_before', 15, 2)->default(0);
            $table->decimal('money_total', 15, 2)->default(0);
            $table->timestamp('created_at')->nullable();
 
            $table->index(['user_id', 'created_at']);
        });
    }
 
    public function down(): void
    {
        Schema::dropIfExists('wallet');
    }
};
