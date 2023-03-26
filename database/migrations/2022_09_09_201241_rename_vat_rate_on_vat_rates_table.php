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
        Schema::table('vat_rates', function (Blueprint $table) {
            $table->renameColumn('rate', 'rate_percentage');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vat_rates', function (Blueprint $table) {
            $table->renameColumn('rate_percentage', 'rate');
        });
    }
};
