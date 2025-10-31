<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('targets', function (Blueprint $table): void {
            $table->boolean('is_group_target')->default(false)->after('priority');
        });
    }

    public function down(): void
    {
        Schema::table('targets', function (Blueprint $table): void {
            $table->dropColumn('is_group_target');
        });
    }
};
