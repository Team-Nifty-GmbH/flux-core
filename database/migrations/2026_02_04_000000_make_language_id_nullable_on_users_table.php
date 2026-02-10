<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('language_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::transaction(function (): void {
            $defaultLanguageId = DB::table('languages')
                ->whereNull('deleted_at')
                ->orderByDesc('is_default')
                ->value('id');

            if ($defaultLanguageId) {
                DB::table('users')->whereNull('language_id')->update(['language_id' => $defaultLanguageId]);
            } else {
                DB::table('users')->whereNull('language_id')->delete();
            }

            Schema::table('users', function (Blueprint $table): void {
                $table->foreignId('language_id')->nullable(false)->change();
            });
        });
    }
};
