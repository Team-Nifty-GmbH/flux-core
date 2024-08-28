<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cart | deleted_by
        Schema::table('carts', function (Blueprint $table) {
            $table->string('deleted_by')->nullable()->after('deleted_at');
        });

        // CommissionRate | created_by, updated_by, deleted_by
        Schema::table('commission_rates', function (Blueprint $table) {
            $table->string('created_by')->nullable()->after('created_at');
            $table->string('updated_by')->nullable()->after('updated_at');
            $table->string('deleted_by')->nullable()->after('deleted_at');
        });

        // Commission | created_by, updated_by
        Schema::table('commissions', function (Blueprint $table) {
            $table->string('created_by')->nullable()->after('created_at');
            $table->string('updated_by')->nullable()->after('updated_at');
        });

        // Schedule | created_by, updated_by, deleted_by
        Schema::table('schedules', function (Blueprint $table) {
            $table->string('created_by')->nullable()->after('created_at');
            $table->string('updated_by')->nullable()->after('updated_at');
            $table->string('deleted_by')->nullable()->after('deleted_at');
        });

        // WorkTimeType | created_by, updated_by, deleted_by
        Schema::table('work_time_types', function (Blueprint $table) {
            $table->string('created_by')->nullable()->after('created_at');
            $table->string('updated_by')->nullable()->after('updated_at');
            $table->string('deleted_by')->nullable()->after('deleted_at');
        });

        // WorkTime | deleted_by
        Schema::table('work_times', function (Blueprint $table) {
            $table->string('deleted_by')->nullable()->after('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropColumn('deleted_by');
        });

        Schema::table('commission_rates', function (Blueprint $table) {
            $table->dropColumn(['created_by', 'updated_by', 'deleted_by']);
        });

        Schema::table('commissions', function (Blueprint $table) {
            $table->dropColumn(['created_by', 'updated_by']);
        });

        Schema::table('schedules', function (Blueprint $table) {
            $table->dropColumn(['created_by', 'updated_by', 'deleted_by']);
        });

        Schema::table('work_time_types', function (Blueprint $table) {
            $table->dropColumn(['created_by', 'updated_by', 'deleted_by']);
        });

        Schema::table('work_times', function (Blueprint $table) {
            $table->dropColumn('deleted_by');
        });
    }
};
