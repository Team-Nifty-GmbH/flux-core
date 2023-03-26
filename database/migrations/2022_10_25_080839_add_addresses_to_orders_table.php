<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('contact_id')
                ->nullable()
                ->after('client_id')
                ->constrained('contacts');

            $table->unsignedBigInteger('language_id')->nullable()->change();

            $table->json('address_invoice')->nullable()->after('tax_exemption_id');
            $table->json('address_delivery')->nullable()->after('address_invoice');
            $table->string('state')->nullable()->after('address_delivery');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['contact_id']);
            $table->unsignedBigInteger('language_id')->nullable(false)->change();
            $table->dropColumn(
                [
                    'address_invoice',
                    'address_delivery',
                    'contact_id',
                    'state',
                ]
            );
        });
    }
};
