<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('discounts', function (Blueprint $table): void {
            $table->string('name')->nullable()->after('model_id');
            $table->decimal('discount_percentage', 40, 10)
                ->nullable()
                ->after('discount');
            $table->decimal('discount_flat', 40, 10)
                ->nullable()
                ->after('discount_percentage');

            $table->renameColumn('sort_number', 'order_column');
        });
    }

    public function down(): void
    {
        Schema::table('discounts', function (Blueprint $table): void {
            $table->renameColumn('order_column', 'sort_number');

            $table->dropColumn([
                'name',
                'discount_percentage',
                'discount_flat',
            ]);
        });
    }
};
