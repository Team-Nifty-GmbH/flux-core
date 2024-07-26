<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_dark_mode')->default(false)->after('is_active');
        });

        Schema::table('addresses', function (Blueprint $table) {
            $table->boolean('is_dark_mode')->default(false)->after('is_invoice_address');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_dark_mode');
        });

        Schema::table('addresses', function (Blueprint $table) {
            $table->dropColumn('is_dark_mode');
        });
    }
};
