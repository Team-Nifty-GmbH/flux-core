<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('widgets', function (Blueprint $table): void {
            $table->unsignedInteger('order_row')->after('order_column')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('widgets', function (Blueprint $table): void {
            $table->dropColumn('order_row');
        });
    }
};
