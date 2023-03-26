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
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropIndex('contacts_customer_number_index');
            $table->unique(['customer_number', 'client_id']);
            $table->unique(['creditor_number', 'client_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropUnique('contacts_customer_number_client_id_unique');
            $table->dropUnique('contacts_creditor_number_client_id_unique');
            $table->index('customer_number');
        });
    }
};
