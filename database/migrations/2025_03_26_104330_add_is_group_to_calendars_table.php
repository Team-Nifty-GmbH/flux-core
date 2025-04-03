<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('calendars', function (Blueprint $table): void {
            $table->boolean('is_group')->default(false)->after('is_editable');
        });
    }

    public function down(): void
    {
        Schema::table('calendars', function (Blueprint $table): void {
            $table->dropColumn('is_group');
        });
    }
};
