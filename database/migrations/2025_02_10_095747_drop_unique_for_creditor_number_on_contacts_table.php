<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropUnique('contacts_creditor_number_client_id_unique');
        });
    }

    public function down(): void
    {
        DB::statement(
            "UPDATE contacts AS c
                INNER JOIN contacts AS j
                    ON c.creditor_number = j.creditor_number
                        AND c.client_id = j.client_id
                SET c.creditor_number = CONCAT(c.creditor_number, ':', c.id)
                WHERE c.creditor_number IS NOT NULL AND c.id > j.id"
        );

        Schema::table('contacts', function (Blueprint $table) {
            $table->unique(['creditor_number', 'client_id']);
        });
    }
};
