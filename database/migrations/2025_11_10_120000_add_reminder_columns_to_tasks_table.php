<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table): void {
            $table->boolean('has_start_reminder')
                ->default(true)
                ->after('due_datetime');
            $table->unsignedInteger('start_reminder_minutes_before')
                ->nullable()
                ->default(15)
                ->after('has_start_reminder');
            $table->dateTime('start_reminder_sent_at')
                ->nullable()
                ->after('start_reminder_minutes_before');
            $table->boolean('has_due_reminder')
                ->default(true)
                ->after('start_reminder_sent_at');
            $table->unsignedInteger('due_reminder_minutes_before')
                ->nullable()
                ->default(15)
                ->after('has_due_reminder');
            $table->dateTime('due_reminder_sent_at')
                ->nullable()
                ->after('due_reminder_minutes_before');
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
