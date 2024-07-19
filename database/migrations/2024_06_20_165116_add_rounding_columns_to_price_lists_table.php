<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('price_lists', function (Blueprint $table) {
            $table->string('rounding_method_enum')->default('none')->after('price_list_code');
            $table->integer('rounding_precision')->nullable()->after('rounding_method_enum');
            $table->unsignedInteger('rounding_number')->nullable()->after('rounding_precision');
            $table->string('rounding_mode')->nullable()->after('rounding_number');
        });
    }

    public function down(): void
    {
        Schema::table('price_lists', function (Blueprint $table) {
            $table->dropColumn([
                'rounding_method_enum',
                'rounding_precision',
                'rounding_number',
                'rounding_mode',
            ]);
        });
    }
};
