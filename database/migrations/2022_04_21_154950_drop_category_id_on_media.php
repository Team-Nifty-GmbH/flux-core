<?php

use FluxErp\Models\Media;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->migrateCategorizablesTable();

        Schema::table('media', function (Blueprint $table) {
            $table->dropForeign('media_category_id_foreign');
            $table->dropColumn('category_id');
        });
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->nullable()->after('uuid');
        });

        $this->rollbackCategorizablesTable();

        Schema::table('media', function (Blueprint $table) {
            $table->foreign('category_id')->references('id')->on('categories');
        });
    }

    private function migrateCategorizablesTable()
    {
        DB::statement('INSERT INTO categorizables(category_id, categorizable_type, categorizable_id)
            SELECT category_id, \''.trim(
            json_encode(Media::class, JSON_UNESCAPED_SLASHES), '"'
        ).'\', id
            FROM media WHERE category_id IS NOT NULL'
        );
    }

    private function rollbackCategorizablesTable()
    {
        DB::statement('UPDATE media
            INNER JOIN categorizables
            ON media.id = categorizables.categorizable_id
            AND categorizables.categorizable_type = \''.trim(
            json_encode(Media::class, JSON_UNESCAPED_SLASHES), '"'
        ).'\'
            SET media.category_id = categorizables.category_id'
        );
    }
};
