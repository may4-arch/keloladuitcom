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
         Schema::dropIfExists('table_budget');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('table_budget', function (Blueprint $table) {
            $table->id('budget_id');
            $table->foreignId('category_id')->constrained('categories', 'category_id')
            ->cascadeOnDelete();
            $table->foreignId('wallet_id')->constrained('wallet', 'wallet_id')
            ->cascadeOnDelete();
           $table->foreignId('user_id')->constrained('users')
            ->cascadeOnDelete();
            
            $table->string('name');
            $table->decimal('maximal', 15, 2);
            $table->decimal('minimal', 15, 2)->default(0);
            $table->unsignedTinyInteger('priority')->default(0);
            $table->timestamps();
        });
    }
};
