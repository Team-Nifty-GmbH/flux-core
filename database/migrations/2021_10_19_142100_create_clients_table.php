<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientsTable extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);
            $table->string('name');
            $table->string('client_code');
            $table->string('ceo');
            $table->string('street');
            $table->string('city');
            $table->string('postcode');
            $table->string('phone');
            $table->string('fax');
            $table->string('email');
            $table->string('website');
            $table->string('bank_name');
            $table->string('bank_code');
            $table->string('bank_account');
            $table->string('bank_iban');
            $table->string('bank_swift');
            $table->string('bank_bic');
            $table->boolean('is_active')->default(true);
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

            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('deleted_by')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
}
