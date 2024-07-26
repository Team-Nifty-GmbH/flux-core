<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('selling_unit', 40, 10)->nullable()->after('dimension_height_mm');
            $table->decimal('basic_unit', 40, 10)->nullable()->after('selling_unit');
            $table->string('time_unit_enum')->nullable()->after('basic_unit');
            $table->boolean('is_service')->default(false)->after('is_bundle');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'selling_unit',
                'basic_unit',
                'time_unit_enum',
                'is_service',
            ]);
        });
    }
};
