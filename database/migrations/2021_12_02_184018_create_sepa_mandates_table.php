<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('sepa_mandates', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('contact_bank_connection_id')->nullable();
            $table->unsignedBigInteger('contact_id');
            $table->string('sepa_mandate_type_enum')->nullable();
            $table->string('mandate_reference_number');
            $table->date('signed_date')->nullable();

            $table->timestamp('created_at')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('deleted_by')->nullable();

            $table->foreign('client_id')
                ->references('id')
                ->on('clients');
            $table->foreign('contact_id')
                ->references('id')
                ->on('contacts');
            $table->foreign('contact_bank_connection_id')
                ->references('id')
                ->on('contact_bank_connections');

            $table->unique(['client_id', 'mandate_reference_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sepa_mandates');
    }
};
