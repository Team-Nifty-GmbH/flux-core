<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsoOnLanguagesTable extends Migration
{
    public function up(): void
    {
        Schema::table('languages', function (Blueprint $table) {
            $table->string('language_code')->unique()->after('name');
            $table->string('iso_name')->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('languages', function (Blueprint $table) {
            $table->dropColumn([
                'iso_name',
                'language_code',
            ]);
        });
    }
}
