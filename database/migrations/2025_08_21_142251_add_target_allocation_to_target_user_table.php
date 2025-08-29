<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('target_user', function (Blueprint $table): void {
            $table->decimal('target_allocation', 15, 4)->nullable()->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('target_user', function (Blueprint $table): void {
            $table->dropColumn('target_allocation');
        });
    }
};
