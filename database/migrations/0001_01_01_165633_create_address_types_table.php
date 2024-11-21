<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('address_types')) {
            return;
        }

        Schema::create('address_types', function (Blueprint $table) {
            $table->id()->comment('Id of the record.');
            $table->char('uuid', 36)->comment('Uuid of the record.');
            $table->unsignedBigInteger('client_id')->comment('A unique identifier number for the table clients.');
            $table->string('address_type_code')->nullable()->comment('Used for special queries or functions, eg. order always need an address with address type \'inv\' ( invoice ).');
            $table->string('name');
            $table->boolean('is_locked')->default(false)->comment('Determines if record can be deleted. True: can not be deleted.');
            $table->boolean('is_unique')->default(false)->comment('Determines if only one of this type can exist in orders or addresses. True: needs to be unique.');
            $table->timestamp('created_at')->nullable()->comment('A timestamp reflecting the time of record-creation.');
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable()->comment('A timestamp reflecting the time of the last change for this record.');
            $table->string('updated_by')->nullable();
            $table->softDeletes()->comment('A timestamp reflecting the time of record-deletion.');
            $table->string('deleted_by')->nullable();

            $table->unique(['client_id', 'address_type_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('address_types');
    }
};
