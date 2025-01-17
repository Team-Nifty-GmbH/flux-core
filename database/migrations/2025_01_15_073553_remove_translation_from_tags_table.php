<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (DB::table('information_schema.table_constraints')
            ->where('table_name', 'tags')
            ->where('constraint_name', 'tags_chk_1')
            ->exists()
        ) {
            DB::statement('ALTER TABLE tags DROP CHECK tags_chk_1');
        }
        if (DB::table('information_schema.table_constraints')
            ->where('table_name', 'tags')
            ->where('constraint_name', 'tags_chk_2')
            ->exists()
        ) {
            DB::statement('ALTER TABLE tags DROP CHECK tags_chk_2');
        }

        Schema::table('tags', function (Blueprint $table) {
            $table->string('name')->change();
            $table->string('slug')->change();
        });

        DB::table('tags')
            ->whereRaw('JSON_VALID(name)')
            ->update([
                'name' => DB::raw('JSON_UNQUOTE(
                    JSON_EXTRACT(
                        name,
                        CONCAT(\'$."\', JSON_UNQUOTE(JSON_EXTRACT(JSON_KEYS(name), \'\\$[0]\')), \'"\')
                    )
                )'),
            ]);

        DB::table('tags')
            ->whereRaw('JSON_VALID(slug)')
            ->update([
                'slug' => DB::raw('JSON_UNQUOTE(
                    JSON_EXTRACT(
                        slug,
                        CONCAT(\'$."\', JSON_UNQUOTE(JSON_EXTRACT(JSON_KEYS(slug), \'\\$[0]\')), \'"\')
                    )
                )'),
            ]);
    }

    public function down(): void
    {
        DB::table('tags')
            ->whereNotNull('name')
            ->update([
                'name' => DB::raw('JSON_OBJECT("' . config('app.locale') . '", name)'),
            ]);

        DB::table('tags')
            ->whereNotNull('slug')
            ->update([
                'slug' => DB::raw('JSON_OBJECT("' . config('app.locale') . '", slug)'),
            ]);

        Schema::table('tags', function (Blueprint $table) {
            $table->json('name')->change();
            $table->json('slug')->change();
        });

        DB::statement('ALTER TABLE tags ADD CONSTRAINT tags_chk_1 CHECK (JSON_VALID(name))');
        DB::statement('ALTER TABLE tags ADD CONSTRAINT tags_chk_2 CHECK (JSON_VALID(slug))');
    }
};
