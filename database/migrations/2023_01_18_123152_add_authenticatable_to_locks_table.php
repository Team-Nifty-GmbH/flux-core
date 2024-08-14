<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('locks')->truncate();

        Schema::table('locks', function (Blueprint $table) {
            $table->string('authenticatable_type')->after('model_id');
            $table->unsignedBigInteger('authenticatable_id')->after('authenticatable_type');
            $table->index(['authenticatable_type', 'authenticatable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('locks', function (Blueprint $table) {
            $table->dropMorphs('authenticatable');
        });
    }
};
