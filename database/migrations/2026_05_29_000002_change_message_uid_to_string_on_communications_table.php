<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('communications', function (Blueprint $table): void {
            $table->string('message_uid')->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('communications')
            ->whereNotNull('message_uid')
            ->whereRaw("message_uid NOT REGEXP '^[0-9]+$'")
            ->update(['message_uid' => null]);

        Schema::table('communications', function (Blueprint $table): void {
            $table->integer('message_uid')->nullable()->change();
        });
    }
};
