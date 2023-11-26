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
                ->after('uuid')
                ->nullable()
                ->constrained()
                ->onDelete('cascade');
            $table->string('name')->after('ended_at');
            $table->integer('paused_time')
                ->after('ended_at')
                ->default(0);
            $table->integer('total_time')
                ->after('paused_time')
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
            $table->dropColumn(['name', 'paused_time', 'is_daily_work_time', 'is_locked', 'total_time']);
        });
    }
};
