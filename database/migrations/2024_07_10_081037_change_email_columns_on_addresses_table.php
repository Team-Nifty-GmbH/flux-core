<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->renameColumn('email', 'email_primary');
            $table->renameColumn('login_name', 'email');
            $table->renameColumn('login_password', 'password');
        });
    }

    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->renameColumn('password', 'login_password');
            $table->renameColumn('email', 'login_name');
            $table->renameColumn('email_primary', 'email');
        });
    }
};
