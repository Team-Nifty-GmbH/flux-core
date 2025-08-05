<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        $this->migrateProjectCategories();

        Schema::table('categories', function (Blueprint $table): void {
            $table->dropColumn('is_project_category');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table): void {
            $table->boolean('is_project_category')->after('sort_number');
        });

        $this->rollbackProjectCategories();
    }

    private function migrateProjectCategories(): void
    {
        DB::table('categories')
            ->where('is_project_category', true)
            ->update(['model' => 'FluxErp\\Models\\ProjectTask']);
    }

    private function rollbackProjectCategories(): void
    {
        DB::table('categories')
            ->where('model', 'FluxErp\\Models\\ProjectTask')
            ->update(['is_project_category' => true]);
    }
};
