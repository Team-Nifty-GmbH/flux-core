<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChangeTranslatableColumnsToJsonOnDocumentTypesTable extends Migration
{
    public function up(): void
    {
        Schema::table('document_types', function (Blueprint $table): void {
            $table->json('name')->change();
            $table->json('description')->nullable()->change();
            $table->json('additional_header')->nullable()->change();
            $table->json('additional_footer')->nullable()->change();
        });

        $this->migrateTranslatable();
    }

    public function down(): void
    {
        $this->rollbackTranslatable();

        Schema::table('document_types', function (Blueprint $table): void {
            $table->string('name')->change();
            $table->string('description')->nullable()->change();
            $table->text('additional_header')->nullable()->change();
            $table->text('additional_footer')->nullable()->change();
        });
    }

    private function migrateTranslatable(): void
    {
        $documentTypes = DB::table('document_types')->get()->toArray();

        array_walk($documentTypes, function (&$item): void {
            $item->name = json_encode([config('app.locale') => $item->name]);
            $item->description = json_encode([config('app.locale') => $item->description]);
            $item->additional_header = json_encode([config('app.locale') => $item->additional_header]);
            $item->additional_footer = json_encode([config('app.locale') => $item->additional_footer]);
            $item = (array) $item;
        });

        DB::table('document_types')->upsert($documentTypes, ['id']);
    }

    private function rollbackTranslatable(): void
    {
        $documentTypes = DB::table('document_types')->get()->toArray();

        array_walk($documentTypes, function (&$item): void {
            $item->name = substr(json_decode($item->name)->{config('app.locale')}, 0, 255);
            $item->description = substr(json_decode($item->description)->{config('app.locale')}, 0, 255);
            $item->additional_header = json_decode($item->additional_header)->{config('app.locale')};
            $item->additional_footer = json_decode($item->additional_footer)->{config('app.locale')};
            $item = (array) $item;
        });

        DB::table('document_types')->upsert($documentTypes, ['id']);
    }
}
