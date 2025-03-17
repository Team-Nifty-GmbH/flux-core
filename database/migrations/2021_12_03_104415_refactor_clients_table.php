<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RefactorClientsTable extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table): void {
            $table->string('ceo')->nullable()->change();
            $table->string('street')->nullable()->change();
            $table->string('city')->nullable()->change();
            $table->string('postcode')->nullable()->change();
            $table->string('phone')->nullable()->change();
            $table->string('fax')->nullable()->change();
            $table->string('email')->nullable()->change();
            $table->string('website')->nullable()->change();
            $table->string('bank_name')->nullable()->change();
            $table->string('bank_code')->nullable()->change();
            $table->string('bank_account')->nullable()->change();
            $table->string('bank_iban')->nullable()->change();
            $table->string('bank_swift')->nullable()->change();
            $table->string('bank_bic')->nullable()->change();

            $table->string('client_code')->unique()->change();
        });

        $this->moveCountryId('uuid');
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table): void {
            $table->string('ceo')->nullable(false)->change();
            $table->string('street')->nullable(false)->change();
            $table->string('city')->nullable(false)->change();
            $table->string('postcode')->nullable(false)->change();
            $table->string('phone')->nullable(false)->change();
            $table->string('fax')->nullable(false)->change();
            $table->string('email')->nullable(false)->change();
            $table->string('website')->nullable(false)->change();
            $table->string('bank_name')->nullable(false)->change();
            $table->string('bank_code')->nullable(false)->change();
            $table->string('bank_account')->nullable(false)->change();
            $table->string('bank_iban')->nullable(false)->change();
            $table->string('bank_swift')->nullable(false)->change();
            $table->string('bank_bic')->nullable(false)->change();

            $table->dropUnique('clients_client_code_unique');
        });

        $this->moveCountryId('deleted_by');
    }

    private function moveCountryId($after): void
    {
        DB::statement('ALTER TABLE clients MODIFY COLUMN country_id BIGINT UNSIGNED AFTER ' . $after);
    }
}
