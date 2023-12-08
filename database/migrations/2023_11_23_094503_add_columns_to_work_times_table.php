<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_times', function (Blueprint $table) {
            $table->foreignId('contact_id')
                ->after('user_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('parent_id')
                ->after('order_position_id')
                ->nullable()
                ->constrained('work_times')
                ->cascadeOnDelete();
            $table->string('name')
                ->after('ended_at')
                ->nullable();
            $table->unsignedBigInteger('paused_time_ms')
                ->after('ended_at')
                ->default(0);
            $table->unsignedBigInteger('total_time_ms')
                ->after('paused_time_ms')
                ->default(0);
            $table->boolean('is_daily_work_time')
                ->after('description')
                ->default(false);
            $table->boolean('is_locked')
                ->after('is_daily_work_time')
                ->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('work_times', function (Blueprint $table) {
            $table->dropConstrainedForeignId('contact_id');
            $table->dropConstrainedForeignId('parent_id');
            $table->dropColumn(['name', 'paused_time_ms', 'is_daily_work_time', 'is_locked', 'total_time_ms']);
        });
    }
};
