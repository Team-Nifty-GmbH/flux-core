<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_id')->nullable()->after('model_id');

            $table->foreign('parent_id')->references('id')->on('media');
        });
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropForeign('media_parent_id_foreign');

            $table->dropColumn('parent_id');
        });
    }
};
