<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChangeValueToJsonOnModelHasValuesTable extends Migration
{
    public function up(): void
    {
        Schema::table('model_has_values', function (Blueprint $table): void {
            $table->json('value')->change();
        });

        $this->migrateName();
    }

    public function down(): void
    {
        $this->rollbackName();

        Schema::table('model_has_values', function (Blueprint $table): void {
            $table->string('value')->change();
        });
    }

    private function migrateName(): void
    {
        $mhv = DB::table('model_has_values')->get()->toArray();

        array_walk($mhv, function (&$item): void {
            $item->value = json_encode([config('app.locale') => $item->value]);
            $item = (array) $item;
        });

        DB::table('model_has_values')->upsert($mhv, ['id']);
    }

    private function rollbackName(): void
    {
        $mhv = DB::table('model_has_values')->get()->toArray();

        array_walk($mhv, function (&$item): void {
            $item->value = substr(json_decode($item->value)->{config('app.locale')}, 0, 255);
            $item = (array) $item;
        });

        DB::table('model_has_values')->upsert($mhv, ['id']);
    }
}
