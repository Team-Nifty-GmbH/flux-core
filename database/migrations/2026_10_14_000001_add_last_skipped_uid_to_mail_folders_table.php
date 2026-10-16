<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('mail_folders', function (Blueprint $table): void {
            $table->unsignedInteger('last_skipped_uid')->nullable()->after('uid_validity');
        });
    }

    public function down(): void
    {
        Schema::table('mail_folders', function (Blueprint $table): void {
            $table->dropColumn('last_skipped_uid');
        });
    }
};
