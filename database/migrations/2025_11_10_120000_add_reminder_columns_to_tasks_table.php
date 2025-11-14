<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table): void {
            $table->unsignedInteger('start_reminder_minutes_before')
                ->nullable()
                ->after('start_time');
            $table->dateTime('start_reminder_sent_at')
                ->nullable()
                ->after('start_reminder_minutes_before');

            $table->unsignedInteger('due_reminder_minutes_before')
                ->nullable()
                ->after('due_time');
            $table->dateTime('due_reminder_sent_at')
                ->nullable()
                ->after('due_reminder_minutes_before');

            $table->boolean('has_due_reminder')
                ->default(false)
                ->after('total_cost');
            $table->boolean('has_start_reminder')
                ->default(false)
                ->after('has_due_reminder');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table): void {
            $table->dropColumn([
                'has_start_reminder',
                'start_reminder_minutes_before',
                'start_reminder_sent_at',
                'has_due_reminder',
                'due_reminder_minutes_before',
                'due_reminder_sent_at',
            ]);
        });
    }
};
