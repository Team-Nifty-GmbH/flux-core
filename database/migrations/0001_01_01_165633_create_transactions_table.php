<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('transactions')) {
            return;
        }

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36);
            $table->unsignedBigInteger('bank_connection_id')->index('transactions_account_id_foreign');
            $table->unsignedBigInteger('currency_id')->index('transactions_currency_id_foreign');
            $table->unsignedBigInteger('parent_id')->nullable()->index('transactions_parent_id_foreign');
            $table->unsignedBigInteger('order_id')->nullable()->index('transactions_order_id_foreign');
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
            $table->timestamp('created_at')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('updated_by')->nullable();
            $table->softDeletes();
            $table->string('deleted_by')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
