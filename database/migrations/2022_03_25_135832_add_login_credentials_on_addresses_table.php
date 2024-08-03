<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->string('login_name')->unique()->nullable()->after('department');
            $table->string('login_password')->nullable()->after('login_name');
            $table->boolean('can_login')->default(false)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropColumn(['login_name', 'login_password', 'can_login']);
        });
    }
};
