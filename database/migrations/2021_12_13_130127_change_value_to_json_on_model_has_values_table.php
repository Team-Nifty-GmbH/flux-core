<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChangeValueToJsonOnModelHasValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('model_has_values', function (Blueprint $table) {
            $table->json('value')->change();
        });

        $this->migrateName();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->rollbackName();

        Schema::table('model_has_values', function (Blueprint $table) {
            $table->string('value')->change();
        });
    }

    private function migrateName()
    {
        $mhv = DB::table('model_has_values')->get()->toArray();

        array_walk($mhv, function (&$item) {
            $item->value = json_encode([config('app.locale') => $item->value]);
            $item = (array) $item;
        });

        DB::table('model_has_values')->upsert($mhv, ['id']);
    }

    private function rollbackName()
    {
        $mhv = DB::table('model_has_values')->get()->toArray();

        array_walk($mhv, function (&$item) {
            $item->value = substr(json_decode($item->value)->{config('app.locale')}, 0, 255);
            $item = (array) $item;
        });

        DB::table('model_has_values')->upsert($mhv, ['id']);
    }
}
