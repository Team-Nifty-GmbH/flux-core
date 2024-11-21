<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meta', function (Blueprint $table) {
            $table->foreign(['additional_column_id'])->references(['id'])->on('additional_columns')->onUpdate('no action')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('meta', function (Blueprint $table) {
            $table->dropForeign('meta_additional_column_id_foreign');
        });
    }
};
