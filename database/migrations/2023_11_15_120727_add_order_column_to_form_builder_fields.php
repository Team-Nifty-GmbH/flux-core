<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form_builder_fields', function (Blueprint $table) {
            $table->dropColumn('ordering');
            $table->integer('order_column')->after('section_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('form_builder_fields', function (Blueprint $table) {
            $table->dropColumn('order_column');
            $table->integer('ordering')->after('section_id')->nullable();
        });
    }
};
