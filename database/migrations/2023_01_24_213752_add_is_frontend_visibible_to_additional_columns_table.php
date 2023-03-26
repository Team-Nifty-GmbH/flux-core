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
        Schema::table('additional_columns', function (Blueprint $table) {
            $table->boolean('is_frontend_visible')->default(true)->after('is_customer_editable');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('additional_columns', function (Blueprint $table) {
            $table->dropColumn('is_frontend_visible');
        });
    }
};
