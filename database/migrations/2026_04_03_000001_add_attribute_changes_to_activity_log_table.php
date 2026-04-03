<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        $tableName = config('activitylog.table_name', 'activity_log');

        if (! Schema::hasColumn($tableName, 'attribute_changes')) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->json('attribute_changes')->nullable()->after('properties');
            });
        }
    }

    public function down(): void
    {
        $tableName = config('activitylog.table_name', 'activity_log');

        Schema::table($tableName, function (Blueprint $table): void {
            $table->dropColumn('attribute_changes');
        });
    }
};
