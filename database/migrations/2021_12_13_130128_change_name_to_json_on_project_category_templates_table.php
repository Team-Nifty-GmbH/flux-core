<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChangeNameToJsonOnProjectCategoryTemplatesTable extends Migration
{
    public function up(): void
    {
        Schema::table('project_category_templates', function (Blueprint $table): void {
            $table->json('name')->change();
        });

        $this->migrateName();
    }

    public function down(): void
    {
        $this->rollbackName();

        Schema::table('project_category_templates', function (Blueprint $table): void {
            $table->string('name')->change();
        });
    }

    private function migrateName(): void
    {
        $templates = DB::table('project_category_templates')->get()->toArray();

        array_walk($templates, function (&$item): void {
            $item->name = json_encode([config('app.locale') => $item->name]);
            $item = (array) $item;
        });

        DB::table('project_category_templates')->upsert($templates, ['id']);
    }

    private function rollbackName(): void
    {
        $templates = DB::table('project_category_templates')->get()->toArray();

        array_walk($templates, function (&$item): void {
            $item->name = substr(json_decode($item->name)->{config('app.locale')}, 0, 255);
            $item = (array) $item;
        });

        DB::table('project_category_templates')->upsert($templates, ['id']);
    }
}
