<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->foreign('currency_id')->references('id')->on('currencies');
            $table->foreign('price_list_id')->references('id')->on('price_lists');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropForeign(['currency_id']);
            $table->dropForeign(['price_list_id']);
        });
    }
};
