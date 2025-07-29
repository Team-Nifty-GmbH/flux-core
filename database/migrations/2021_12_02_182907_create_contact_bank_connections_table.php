<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('contact_bank_connections', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);
            $table->unsignedBigInteger('contact_id');
            $table->string('iban')->nullable();
            $table->string('account_holder')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bic')->nullable();
            $table->decimal('balance', 40, 10)->nullable();
            $table->boolean('is_credit_account')->default(false);
            $table->timestamp('created_at')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('deleted_by')->nullable();

            $table->foreign('contact_id')->references('id')->on('contacts');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_bank_connections');
    }
};
