<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('serial_number_ranges', function (Blueprint $table) {
            $table->renameColumn('affix', 'suffix');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('serial_number_ranges', function (Blueprint $table) {
            $table->renameColumn('suffix', 'affix');
        });
    }
};
