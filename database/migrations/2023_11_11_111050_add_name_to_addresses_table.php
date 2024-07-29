<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->string('name')->after('lastname')->nullable();
        });

        DB::statement('UPDATE addresses
            SET name = TRIM(
                          CONCAT_WS(", ",
                            NULLIF(TRIM(company), ""),
                            NULLIF(TRIM(CONCAT_WS(" ", firstname, lastname)), "")
                          )
                        )
            WHERE name IS NULL
            AND (company IS NOT NULL OR firstname IS NOT NULL OR lastname IS NOT NULL)'
        );
    }

    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropColumn(['name']);
        });
    }
};
