<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sepa_mandates')) {
            return;
        }

        Schema::create('sepa_mandates', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36);
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('contact_id')->index('sepa_mandates_contact_id_foreign');
            $table->unsignedBigInteger('contact_bank_connection_id')->index('sepa_mandates_bank_connection_id_foreign');
            $table->string('mandate_reference_number');
            $table->date('signed_date')->nullable();
            $table->timestamp('created_at')->nullable()->comment('A timestamp reflecting the time of record-creation.');
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable()->comment('A timestamp reflecting the time of the last change for this record.');
            $table->string('updated_by')->nullable();
            $table->softDeletes()->comment('A timestamp reflecting the time of record-deletion.');
            $table->string('deleted_by')->nullable();

            $table->unique(['client_id', 'mandate_reference_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sepa_mandates');
    }
};
