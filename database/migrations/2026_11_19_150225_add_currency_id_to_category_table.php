<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('categories', 'currency_id')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->unsignedBigInteger('currency_id')
                      ->default(1)
                      ->after('user_id');
            });
        }

        DB::table('categories')->update(['currency_id' => 1]);

        Schema::table('categories', function (Blueprint $table) {
            $table->foreign('currency_id')
                  ->references('id')
                  ->on('currencies')
                  ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['currency_id']);
            $table->dropColumn('currency_id');
        });
    }
};
