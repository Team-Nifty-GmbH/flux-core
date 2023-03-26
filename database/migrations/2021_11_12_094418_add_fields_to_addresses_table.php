<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->string('salutation')->nullable()->after('company');
            $table->string('zip')->nullable()->after('lastname');
            $table->string('city')->nullable()->after('zip');
            $table->string('street')->nullable()->after('city');
            $table->string('url')->nullable()->after('street');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropColumn(['salutation', 'zip', 'city', 'street', 'url']);
        });
    }
}
