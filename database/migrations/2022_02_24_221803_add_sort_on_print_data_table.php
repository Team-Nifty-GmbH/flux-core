<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('print_data', function (Blueprint $table) {
            $table->integer('sort')->after('template_name')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('print_data', function (Blueprint $table) {
            $table->dropColumn('sort');
        });
    }
};
