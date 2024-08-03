<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveNullablesOnCountriesTable extends Migration
{
    public function up(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->string('iso_alpha3')->nullable()->change();
            $table->string('iso_numeric')->nullable()->change();
            $table->string('iso_alpha2')->unique()->change();
        });
    }

    public function down(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->string('iso_alpha3')->nullable(false)->change();
            $table->string('iso_numeric')->nullable(false)->change();
            $table->dropUnique('countries_iso_alpha2_unique');
        });
    }
}
