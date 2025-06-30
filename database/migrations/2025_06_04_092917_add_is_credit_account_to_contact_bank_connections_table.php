<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('contact_bank_connections', function (Blueprint $table): void {
            $table->string('iban')->nullable()->change();
            $table->decimal('balance', 40, 10)->nullable()->after('bic');
            $table->boolean('is_credit_account')->default(false)->after('balance');
        });
    }

    public function down(): void
    {
        DB::table('contact_bank_connections')
            ->whereNull('iban')
            ->delete();

        Schema::table('contact_bank_connections', function (Blueprint $table): void {
            $table->string('iban')->nullable(false)->change();
            $table->dropColumn('is_credit_account');
        });
    }
};
