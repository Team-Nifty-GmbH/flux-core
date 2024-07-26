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
        Schema::table('additional_columns', function (Blueprint $table) {
            $table->boolean('is_customer_editable')
                ->default(false)
                ->after('values')
                ->comment('If set to true the customer can edit this field in the customer portal.');
            $table->string('label')->nullable()->change();
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
            $table->dropColumn('is_customer_editable');
            $table->json('label')->nullable()->change();
        });
    }
};
