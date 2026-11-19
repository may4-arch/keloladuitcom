<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->string('name', 100);
            $table->enum('type', [
                'cash',
                'bank',
                'ewallet',
                'credit',
                'investment',
                'other'
            ])->default('bank');

            // Foreign key ke currencies.id
            $table->foreignId('currency_id')
                  ->constrained('currencies')
                  ->restrictOnDelete();

            $table->decimal('opening_balance', 15, 2)->default(0.00);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};