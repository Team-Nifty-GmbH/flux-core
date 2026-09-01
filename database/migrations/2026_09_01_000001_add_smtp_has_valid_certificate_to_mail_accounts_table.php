<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('mail_accounts', function (Blueprint $table): void {
            $table->boolean('smtp_has_valid_certificate')->default(true)->after('smtp_encryption');
        });
    }

    public function down(): void
    {
        Schema::table('mail_accounts', function (Blueprint $table): void {
            $table->dropColumn('smtp_has_valid_certificate');
        });
    }
};
