<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('bank_connections', function (Blueprint $table): void {
            $table->boolean('is_virtual')->default(false)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('bank_connections', function (Blueprint $table): void {
            $table->dropColumn('is_virtual');
        });
    }
};
