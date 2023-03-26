<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSepaMandatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sepa_mandates', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('contact_id');
            $table->unsignedBigInteger('contact_bank_connection_id');
            $table->date('signed_date')->nullable();
            $table->timestamp('created_at')->nullable()
                ->comment('A timestamp reflecting the time of record-creation.');
            $table->unsignedBigInteger('created_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that created this record.');
            $table->timestamp('updated_at')->nullable()
                ->comment('A timestamp reflecting the time of the last change for this record.');
            $table->unsignedBigInteger('updated_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that changed this record last.');
            $table->timestamp('deleted_at')->nullable()
                ->comment('A timestamp reflecting the time of record-deletion.');
            $table->unsignedBigInteger('deleted_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that deleted this record.');

            $table->foreign('client_id')
                ->references('id')
                ->on('clients');
            $table->foreign('contact_id')
                ->references('id')
                ->on('contacts');
            $table->foreign('contact_bank_connection_id')
                ->references('id')
                ->on('contact_bank_connections');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('deleted_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sepa_mandates');
    }
}
