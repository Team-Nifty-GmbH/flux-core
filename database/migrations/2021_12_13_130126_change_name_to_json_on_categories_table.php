<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChangeNameToJsonOnCategoriesTable extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->json('name')->change();
        });

        $this->migrateName();
    }

    public function down(): void
    {
        $this->rollbackName();

        Schema::table('categories', function (Blueprint $table) {
            $table->string('name')->change();
        });
    }

    private function migrateName(): void
    {
        $categories = DB::table('categories')->get()->toArray();

        array_walk($categories, function (&$item) {
            $item->name = json_encode([config('app.locale') => $item->name]);
            $item = (array) $item;
        });

        DB::table('categories')->upsert($categories, ['id']);
    }

    private function rollbackName(): void
    {
        $categories = DB::table('categories')->get()->toArray();

        array_walk($categories, function (&$item) {
            $item->name = substr(json_decode($item->name)->{config('app.locale')}, 0, 255);
            $item = (array) $item;
        });

        DB::table('categories')->upsert($categories, ['id']);
    }
}
