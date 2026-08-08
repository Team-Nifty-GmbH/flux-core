<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('media_folders', function (Blueprint $table): void {
            $table->string('collection_name')
                ->nullable()
                ->after('parent_id');
        });
    }

    public function down(): void
    {
        Schema::table('media_folders', function (Blueprint $table): void {
            $table->dropColumn('collection_name');
        });
    }
};
