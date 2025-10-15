<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('target_user', function (Blueprint $table): void {
            $table->decimal('target_share', 40, 10)->nullable()->after('user_id');
            $table->boolean('is_percentage')->nullable()->after('target_share');
        });
    }

    public function down(): void
    {
        Schema::table('target_user', function (Blueprint $table): void {
            $table->dropColumn(['target_share', 'is_percentage']);
        });
    }
};
