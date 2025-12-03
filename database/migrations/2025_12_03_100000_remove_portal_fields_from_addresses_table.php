<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('addresses', function (Blueprint $table): void {
            $table->dropColumn(['email', 'password', 'can_login', 'remember_token']);
        });
    }

    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table): void {
            $table->string('email')->nullable()->after('language_id');
            $table->string('password')->nullable()->after('email');
            $table->boolean('can_login')->default(false)->after('is_active');
            $table->string('remember_token', 100)->nullable()->after('can_login');
        });
    }
};
