<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('payment_types', function (Blueprint $table): void {
            $table->boolean('is_cash')->default(false)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('payment_types', function (Blueprint $table): void {
            $table->dropColumn('is_cash');
        });
    }
};
