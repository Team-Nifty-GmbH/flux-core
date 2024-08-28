<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RefactorCountriesTable extends Migration
{
    public function up(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->boolean('is_default')->default(false)->change();
        });

        $this->moveIsoNumeric('iso_alpha3');
    }

    public function down(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->boolean('is_default')->default(true)->change();
        });

        $this->moveIsoNumeric('currency_id');
    }

    private function moveIsoNumeric($after)
    {
        DB::statement('ALTER TABLE countries MODIFY COLUMN iso_numeric VARCHAR(255) AFTER ' . $after);
    }
}
