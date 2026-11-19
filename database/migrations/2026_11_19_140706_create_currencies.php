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
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->char('code', 3)->unique();              // ISO 4217: IDR, USD, SGD, EUR
            $table->string('name', 50);                     // Indonesian Rupiah, US Dollar
            $table->string('symbol', 10);                   // Rp, $, S$, €
            $table->enum('symbol_position', ['prefix', 'suffix'])->default('prefix');
            $table->unsignedTinyInteger('decimal_places')->default(2); // 0 atau 2 untuk IDR, 2 untuk USD
            $table->char('thousand_separator', 1)->default('.');
            $table->char('decimal_separator', 1)->default(',');
            $table->decimal('exchange_rate', 18, 6)->default(1.000000); // Kurs terhadap base currency
            $table->boolean('is_base')->default(false);     // Penanda mata uang utama (misal: IDR)
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
