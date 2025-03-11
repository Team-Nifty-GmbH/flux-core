<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('iban')->nullable()->after('user_code');
            $table->string('account_holder')->nullable()->after('iban');
            $table->string('bank_name')->nullable()->after('account_holder');
            $table->string('bic')->nullable()->after('bank_name');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['iban', 'account_holder', 'bank_name', 'bic']);
        });
    }
};
