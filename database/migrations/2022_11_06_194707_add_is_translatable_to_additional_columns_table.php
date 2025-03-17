<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('additional_columns', function (Blueprint $table): void {
            $table->boolean('is_translatable')->default(false)->after('values');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('additional_columns', function (Blueprint $table): void {
            $table->dropColumn('is_translatable');
        });
    }
};
