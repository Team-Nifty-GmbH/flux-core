<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('order_positions', function (Blueprint $table) {
            $table->foreignId('created_from_id')
                ->after('parent_id')
                ->nullable()
                ->constrained('order_positions')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('order_positions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_from_id');
        });
    }
};
