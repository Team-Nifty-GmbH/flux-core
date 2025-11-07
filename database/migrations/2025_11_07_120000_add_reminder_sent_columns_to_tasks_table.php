<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table): void {
            $table->dateTime('due_reminder_sent_at')->nullable()->after('due_datetime');
            $table->dateTime('start_reminder_sent_at')->nullable()->after('due_reminder_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table): void {
            $table->dropColumn([
                'due_reminder_sent_at',
                'start_reminder_sent_at',
            ]);
        });
    }
};
