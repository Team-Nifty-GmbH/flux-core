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
        Schema::table('stock_postings', function (Blueprint $table) {
            $table->decimal('purchase_price', 40, 10)->nullable()->after('posting')
                ->comment('The full price paid for the entirety of this stock posting.');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stock_postings', function (Blueprint $table) {
            $table->dropColumn('purchase_price');
        });
    }
};
