<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->decimal('subtotal_net_price', 40, 10)
                ->nullable()
                ->after('margin');
            $table->decimal('subtotal_gross_price', 40, 10)
                ->nullable()
                ->after('subtotal_net_price');
            $table->json('subtotal_vats')
                ->nullable()
                ->after('subtotal_gross_price');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn([
                'subtotal_net_price',
                'subtotal_gross_price',
                'subtotal_vats',
            ]);
        });
    }
};
