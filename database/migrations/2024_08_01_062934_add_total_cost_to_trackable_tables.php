<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('work_times', function (Blueprint $table) {
            $table->decimal('total_cost', 10)->nullable()->after('total_time_ms');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->decimal('total_cost', 10)->nullable()->after('budget');
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->decimal('total_cost', 10)->nullable()->after('state');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('total_cost', 40, 10)->nullable()->after('total_purchase_price');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->decimal('total_cost', 40, 10)->nullable()->after('budget');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('total_cost');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('total_cost');
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('total_cost');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('total_cost');
        });

        Schema::table('work_times', function (Blueprint $table) {
            $table->dropColumn('total_cost');
        });
    }
};
