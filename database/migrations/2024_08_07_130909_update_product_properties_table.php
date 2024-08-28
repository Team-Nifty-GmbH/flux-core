<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('product_properties', function (Blueprint $table) {
            $table->foreignId('product_property_group_id')
                ->nullable()
                ->after('uuid')
                ->constrained('product_property_groups')
                ->nullOnDelete();

            $table->string('property_type_enum')
                ->default('text')
                ->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('product_properties', function (Blueprint $table) {
            $table->dropForeign(['product_property_group_id']);
            $table->dropColumn(['product_property_group_id', 'property_type_enum']);
        });
    }
};
