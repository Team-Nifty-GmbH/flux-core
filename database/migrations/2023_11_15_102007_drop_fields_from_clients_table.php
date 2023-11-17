<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'bank_name',
                'bank_code',
                'bank_account',
                'bank_iban',
                'bank_swift',
                'bank_bic',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('bank_name')->nullable()->after('website');
            $table->string('bank_code')->nullable()->after('bank_name');
            $table->string('bank_account')->nullable()->after('bank_code');
            $table->string('bank_iban')->nullable()->after('bank_account');
            $table->string('bank_swift')->nullable()->after('bank_iban');
            $table->string('bank_bic')->nullable()->after('bank_swift');
        });
    }
};
