<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChangeNameToJsonOnCountryRegionsTable extends Migration
{
    public function up(): void
    {
        Schema::table('country_regions', function (Blueprint $table): void {
            $table->json('name')->change();
        });

        $this->migrateName();
    }

    public function down(): void
    {
        $this->rollbackName();

        Schema::table('country_regions', function (Blueprint $table): void {
            $table->string('name')->change();
        });
    }

    private function migrateName(): void
    {
        $regions = DB::table('country_regions')->get()->toArray();

        array_walk($regions, function (&$item): void {
            $item->name = json_encode([config('app.locale') => $item->name]);
            $item = (array) $item;
        });

        DB::table('country_regions')->upsert($regions, ['id']);
    }

    private function rollbackName(): void
    {
        $regions = DB::table('country_regions')->get()->toArray();

        array_walk($regions, function (&$item): void {
            $item->name = substr(json_decode($item->name)->{config('app.locale')}, 0, 255);
            $item = (array) $item;
        });

        DB::table('country_regions')->upsert($regions, ['id']);
    }
}
