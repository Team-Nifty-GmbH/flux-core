<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChangeNameToJsonOnCountryRegionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('country_regions', function (Blueprint $table) {
            $table->json('name')->change();
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

        Schema::table('country_regions', function (Blueprint $table) {
            $table->string('name')->change();
        });
    }

    private function migrateName()
    {
        $regions = DB::table('country_regions')->get()->toArray();

        array_walk($regions, function (&$item) {
            $item->name = json_encode([config('app.locale') => $item->name]);
            $item = (array) $item;
        });

        DB::table('country_regions')->upsert($regions, ['id']);
    }

    private function rollbackName()
    {
        $regions = DB::table('country_regions')->get()->toArray();

        array_walk($regions, function (&$item) {
            $item->name = substr(json_decode($item->name)->{config('app.locale')}, 0, 255);
            $item = (array) $item;
        });

        DB::table('country_regions')->upsert($regions, ['id']);
    }
}
