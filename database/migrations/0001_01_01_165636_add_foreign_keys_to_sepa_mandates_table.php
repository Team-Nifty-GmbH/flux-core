<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('sepa_mandates', function (Blueprint $table) {
            $table->foreign(['contact_bank_connection_id'], 'sepa_mandates_bank_connection_id_foreign')->references(['id'])->on('contact_bank_connections')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['client_id'])->references(['id'])->on('clients')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['contact_id'])->references(['id'])->on('contacts')->onUpdate('no action')->onDelete('no action');
        });
    }

    public function down(): void
    {
        Schema::table('sepa_mandates', function (Blueprint $table) {
            $table->dropForeign('sepa_mandates_bank_connection_id_foreign');
            $table->dropForeign('sepa_mandates_client_id_foreign');
            $table->dropForeign('sepa_mandates_contact_id_foreign');
        });
    }
};
