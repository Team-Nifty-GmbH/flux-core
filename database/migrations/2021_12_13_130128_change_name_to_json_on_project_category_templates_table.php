<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChangeNameToJsonOnProjectCategoryTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('project_category_templates', function (Blueprint $table) {
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

        Schema::table('project_category_templates', function (Blueprint $table) {
            $table->string('name')->change();
        });
    }

    private function migrateName()
    {
        $templates = DB::table('project_category_templates')->get()->toArray();

        array_walk($templates, function (&$item) {
            $item->name = json_encode([config('app.locale') => $item->name]);
            $item = (array) $item;
        });

        DB::table('project_category_templates')->upsert($templates, ['id']);
    }

    private function rollbackName()
    {
        $templates = DB::table('project_category_templates')->get()->toArray();

        array_walk($templates, function (&$item) {
            $item->name = substr(json_decode($item->name)->{config('app.locale')}, 0, 255);
            $item = (array) $item;
        });

        DB::table('project_category_templates')->upsert($templates, ['id']);
    }
}
