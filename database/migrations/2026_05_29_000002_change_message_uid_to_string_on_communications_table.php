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
        $nonNumericIds = DB::table('communications')
            ->whereNotNull('message_uid')
            ->pluck('message_uid', 'id')
            ->reject(fn (string $uid) => ctype_digit($uid))
            ->keys()
            ->all();

        if ($nonNumericIds) {
            DB::table('communications')
                ->whereIntegerInRaw('id', $nonNumericIds)
                ->update(['message_uid' => null]);
        }

        Schema::table('communications', function (Blueprint $table): void {
            $table->integer('message_uid')->nullable()->change();
        });
    }
};
