<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveRequestHashColumnFromPrintData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('print_data', function (Blueprint $table) {
            $table->dropColumn('request_hash');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('print_data', function (Blueprint $table) {
            $table->string('request_hash')->unique()->index()->after('template_name');
        });
    }
}
