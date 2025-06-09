<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('addresses', function (Blueprint $table): void {
            $table->json('search_aliases')->after('password')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table): void {
            $table->dropColumn('search_aliases');
        });
    }
};
