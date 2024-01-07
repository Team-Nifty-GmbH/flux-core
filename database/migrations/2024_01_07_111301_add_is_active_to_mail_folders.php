<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mail_folders', function (Blueprint $table) {
            $table->boolean('is_active')->after('slug')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('mail_folders', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};
