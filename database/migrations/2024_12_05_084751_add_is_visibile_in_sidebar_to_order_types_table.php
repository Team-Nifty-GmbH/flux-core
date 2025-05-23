<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('order_types', function (Blueprint $table): void {
            $table->boolean('is_visible_in_sidebar')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('order_types', function (Blueprint $table): void {
            $table->dropColumn('is_visible_in_sidebar');
        });
    }
};
