<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('mail_account_user', function (Blueprint $table): void {
            $table->boolean('is_default')->default(false)->after('mail_account_id');
        });
    }

    public function down(): void
    {
        Schema::table('mail_account_user', function (Blueprint $table): void {
            $table->dropColumn('is_default');
        });
    }
};
