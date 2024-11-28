<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('taggables', function (Blueprint $table) {
            $table->foreign(['tag_id'])->references(['id'])->on('tags')->onUpdate('no action')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('taggables', function (Blueprint $table) {
            $table->dropForeign('taggables_tag_id_foreign');
        });
    }
};
