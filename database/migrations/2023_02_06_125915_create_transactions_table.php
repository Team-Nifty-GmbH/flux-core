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
        Schema::create('transactions', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);
            $table->unsignedBigInteger('account_id');
            $table->unsignedBigInteger('currency_id');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->date('value_date');
            $table->date('booking_date');
            $table->decimal('amount', 40, 10);
            $table->string('purpose')->nullable();
            $table->string('type')->nullable();
            $table->string('counterpart_name')->nullable();
            $table->string('counterpart_account_number')->nullable();
            $table->string('counterpart_iban')->nullable();
            $table->string('counterpart_bic')->nullable();
            $table->string('counterpart_bank_name')->nullable();
            $table->timestamps();

            $table->foreign('account_id')->references('id')->on('accounts');
            $table->foreign('currency_id')->references('id')->on('currencies');
            $table->foreign('parent_id')->references('id')->on('transactions');
            $table->foreign('order_id')->references('id')->on('orders');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
