<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChangeNameToJsonOnProjectTasksTable extends Migration
{
    public function up(): void
    {
        Schema::table('project_tasks', function (Blueprint $table) {
            $table->json('name')->change();
        });

        $this->migrateName();
    }

    public function down(): void
    {
        $this->rollbackName();

        Schema::table('project_tasks', function (Blueprint $table) {
            $table->string('name')->change();
        });
    }

    private function migrateName()
    {
        $tasks = DB::table('project_tasks')->get()->toArray();

        array_walk($tasks, function (&$item) {
            $item->name = json_encode([config('app.locale') => $item->name]);
            $item = (array) $item;
        });

        DB::table('project_tasks')->upsert($tasks, ['id']);
    }

    private function rollbackName()
    {
        $tasks = DB::table('project_tasks')->get()->toArray();

        array_walk($tasks, function (&$item) {
            $item->name = substr(json_decode($item->name)->{config('app.locale')}, 0, 255);
            $item = (array) $item;
        });

        DB::table('project_tasks')->upsert($tasks, ['id']);
    }
}
