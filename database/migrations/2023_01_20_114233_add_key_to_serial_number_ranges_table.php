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
        Schema::table('serial_number_ranges', function (Blueprint $table) {
            $table->string('unique_key')->after('uuid');
            $table->dropUnique(['model_type', 'model_id', 'type', 'client_id']);
        });

        DB::statement('UPDATE serial_number_ranges SET unique_key = CONCAT_WS(".", model_type, model_id, type, client_id) WHERE 1');

        Schema::table('serial_number_ranges', function (Blueprint $table) {
            $table->unique('unique_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('serial_number_ranges', function (Blueprint $table) {
            $table->dropColumn('unique_key');
            $table->unique(['model_type', 'model_id', 'type', 'client_id']);
        });
    }
};
