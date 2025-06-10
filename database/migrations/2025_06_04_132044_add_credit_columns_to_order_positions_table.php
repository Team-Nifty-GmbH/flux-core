<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('order_positions', function (Blueprint $table): void {
            $table->unsignedBigInteger('credit_account_id')->nullable()->after('created_from_id');
            $table->decimal('credit_amount', 40, 10)->nullable()->after('product_prices');
            $table->tinyInteger('post_on_credit_account')->nullable()->after('credit_amount')
                ->comment('> 0: credit, < 0: debit \'credit_amount\' on the given credit account.'
                    . ' 0 or null: no transaction.'
                );

            $table->foreign('credit_account_id')
                ->references('id')
                ->on('contact_bank_connections')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('order_positions', function (Blueprint $table): void {
            $table->dropForeign(['credit_account_id']);
            $table->dropColumn([
                'credit_account_id',
                'credit_amount',
                'post_on_credit_account',
            ]);
        });
    }
};
