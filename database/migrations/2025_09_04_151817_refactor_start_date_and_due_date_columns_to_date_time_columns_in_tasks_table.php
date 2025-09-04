<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table): void {
            $table->time('start_time')->nullable()->after('due_date');
            $table->time('due_time')->nullable()->after('start_time');
            $table->dateTime('start_timestamp')->nullable()->after('due_time');
            $table->dateTime('due_timestamp')->nullable()->after('start_timestamp');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table): void {
            $table->dropColumn([
                'start_time',
                'due_time',
                'start_timestamp',
                'due_timestamp',
            ]);
        });
    }
};
