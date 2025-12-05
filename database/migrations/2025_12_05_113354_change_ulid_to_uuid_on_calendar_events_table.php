<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('calendar_events', function (Blueprint $table): void {
            $table->char('ulid', 36)->change();
            $table->renameColumn('ulid', 'uuid');
        });
    }

    public function down(): void
    {
        DB::table('calendar_events')
            ->update([
                'uuid' => DB::raw('SUBSTRING(uuid, 1, 26)'),
            ]);

        Schema::table('calendar_events', function (Blueprint $table): void {
            $table->char('uuid', 26)->change();
            $table->renameColumn('uuid', 'ulid');
        });
    }
};
