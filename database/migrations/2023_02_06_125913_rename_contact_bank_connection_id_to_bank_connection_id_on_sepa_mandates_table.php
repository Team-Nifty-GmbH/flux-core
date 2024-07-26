<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sepa_mandates', function (Blueprint $table) {
            $table->dropForeign('sepa_mandates_contact_bank_connection_id_foreign');
            $table->dropIndex('sepa_mandates_contact_bank_connection_id_foreign');
            $table->renameColumn('contact_bank_connection_id', 'bank_connection_id');
            $table->foreign('bank_connection_id')->references('id')->on('bank_connections');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sepa_mandates', function (Blueprint $table) {
            $table->dropForeign('sepa_mandates_bank_connection_id_foreign');
            $table->dropIndex('sepa_mandates_bank_connection_id_foreign');
            $table->renameColumn('bank_connection_id', 'contact_bank_connection_id');
            $table->foreign('contact_bank_connection_id')
                ->references('id')
                ->on('bank_connections');
        });
    }
};
