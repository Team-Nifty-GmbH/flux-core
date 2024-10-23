<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('vat_rates', function (Blueprint $table) {
            $table->boolean('is_default')->default(false)->after('footer_text');
        });
    }

    public function down(): void
    {
        Schema::table('vat_rates', function (Blueprint $table) {
            $table->dropColumn('is_default');
        });
    }
};
