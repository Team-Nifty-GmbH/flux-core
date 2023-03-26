<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveNullablesOnCountriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->string('iso_alpha3')->nullable()->change();
            $table->string('iso_numeric')->nullable()->change();
            $table->string('iso_alpha2')->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->string('iso_alpha3')->nullable(false)->change();
            $table->string('iso_numeric')->nullable(false)->change();
            $table->dropUnique('countries_iso_alpha2_unique');
        });
    }
}
