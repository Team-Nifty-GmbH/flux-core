<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChangeNameToJsonOnCountriesTable extends Migration
{
    public function up(): void
    {
        Schema::table('countries', function (Blueprint $table): void {
            $table->json('name')->change();
        });

        $this->migrateName();
    }

    public function down(): void
    {
        $this->rollbackName();

        Schema::table('countries', function (Blueprint $table): void {
            $table->string('name')->change();
        });
    }

    private function migrateName(): void
    {
        $countries = DB::table('countries')->get()->toArray();

        array_walk($countries, function (&$item): void {
            $item->name = json_encode([config('app.locale') => $item->name]);
            $item = (array) $item;
        });

        DB::table('countries')->upsert($countries, ['id']);
    }

    private function rollbackName(): void
    {
        $countries = DB::table('countries')->get()->toArray();

        array_walk($countries, function (&$item): void {
            $item->name = substr(json_decode($item->name)->{config('app.locale')}, 0, 255);
            $item = (array) $item;
        });

        DB::table('countries')->upsert($countries, ['id']);
    }
}
