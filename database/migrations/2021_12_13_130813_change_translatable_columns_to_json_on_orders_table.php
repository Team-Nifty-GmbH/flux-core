<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChangeTranslatableColumnsToJsonOnOrdersTable extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->json('header')->nullable()->change();
            $table->json('footer')->nullable()->change();
            $table->json('logistic_note')->nullable()->change();
        });

        $this->migrateTranslatable();
    }

    public function down(): void
    {
        $this->rollbackTranslatable();

        Schema::table('orders', function (Blueprint $table) {
            $table->text('header')->nullable()->change();
            $table->text('footer')->nullable()->change();
            $table->text('logistic_note')->nullable()->change();
        });
    }

    private function migrateTranslatable(): void
    {
        $orders = DB::table('orders')->get()->toArray();

        array_walk($orders, function (&$item) {
            $item->header = json_encode([config('app.locale') => $item->header]);
            $item->footer = json_encode([config('app.locale') => $item->footer]);
            $item->logistic_note = json_encode([config('app.locale') => $item->logistic_note]);
            $item = (array) $item;
        });

        DB::table('orders')->upsert($orders, ['id']);
    }

    private function rollbackTranslatable(): void
    {
        $orders = DB::table('orders')->get()->toArray();

        array_walk($orders, function (&$item) {
            $item->header = json_decode($item->header)->{config('app.locale')};
            $item->footer = json_decode($item->footer)->{config('app.locale')};
            $item->logistic_note = json_decode($item->logistic_note)->{config('app.locale')};
            $item = (array) $item;
        });

        DB::table('orders')->upsert($orders, ['id']);
    }
}
