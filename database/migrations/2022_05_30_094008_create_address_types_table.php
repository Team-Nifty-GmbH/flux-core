<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddressTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('address_types', function (Blueprint $table) {
            $table->id()
                ->comment('Id of the record.');
            $table->char('uuid', 36)
                ->comment('Uuid of the record.');
            $table->unsignedBigInteger('client_id')
                ->comment('A unique identifier number for the table clients.');
            $table->string('address_type_code')->nullable()
                ->comment('Used for special queries or functions, eg. order always need an address with address type \'inv\' ( invoice ).');
            $table->text('name')
                ->comment('Name of the address type.');
            $table->boolean('is_locked')->default(false)
                ->comment('Determines if record can be deleted. True: can not be deleted.');
            $table->boolean('is_unique')->default(false)
                ->comment('Determines if only one of this type can exist in orders or addresses. True: needs to be unique.');
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

            $table->foreign('client_id')->references('id')->on('clients');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('deleted_by')->references('id')->on('users');

            $table->unique(['client_id', 'address_type_code']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('address_types');
    }
}
