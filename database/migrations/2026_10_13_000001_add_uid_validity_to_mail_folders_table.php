<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('mail_folders', function (Blueprint $table): void {
            $table->unsignedInteger('uid_validity')->nullable()->after('delta_link');
        });
    }

    public function down(): void
    {
        Schema::table('mail_folders', function (Blueprint $table): void {
            $table->dropColumn('uid_validity');
        });
    }
};
