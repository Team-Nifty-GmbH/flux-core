<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChangeNameAndDescriptionToJsonOnPaymentTypesTable extends Migration
{
    public function up(): void
    {
        Schema::table('payment_types', function (Blueprint $table) {
            $table->json('name')->change();
            $table->json('description')->nullable()->change();
        });

        $this->migrateNameAndDescription();
    }

    public function down(): void
    {
        $this->rollbackNameAndDescription();

        Schema::table('payment_types', function (Blueprint $table) {
            $table->string('name')->change();
            $table->string('description')->nullable()->change();
        });
    }

    private function migrateNameAndDescription(): void
    {
        $paymentTypes = DB::table('payment_types')->get()->toArray();

        array_walk($paymentTypes, function (&$item) {
            $item->name = json_encode([config('app.locale') => $item->name]);
            $item->description = json_encode([config('app.locale') => $item->description]);
            $item = (array) $item;
        });

        DB::table('payment_types')->upsert($paymentTypes, ['id']);
    }

    private function rollbackNameAndDescription(): void
    {
        $paymentTypes = DB::table('payment_types')->get()->toArray();

        array_walk($paymentTypes, function (&$item) {
            $item->name = substr(json_decode($item->name)->{config('app.locale')}, 0, 255);
            $item->description = substr(json_decode($item->description)->{config('app.locale')}, 0, 255);
            $item = (array) $item;
        });

        DB::table('payment_types')->upsert($paymentTypes, ['id']);
    }
}
