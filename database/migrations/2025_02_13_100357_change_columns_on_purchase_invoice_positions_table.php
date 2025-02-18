<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_invoice_positions', function (Blueprint $table) {
            $table->decimal('amount', 40, 10)->change();
            $table->decimal('unit_price', 40, 10)->nullable()->change();
            $table->decimal('total_price', 40, 10)->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('purchase_invoice_positions')
            ->update([
                'amount' => DB::raw('ABS(amount)'),
                'unit_price' => DB::raw('ABS(unit_price)'),
                'total_price' => DB::raw('ABS(total_price)'),
            ]);

        Schema::table('purchase_invoice_positions', function (Blueprint $table) {
            $table->decimal('amount', 40, 10)->unsigned()->change();
            $table->decimal('unit_price', 40, 10)->nullable()->unsigned()->change();
            $table->decimal('total_price', 40, 10)->nullable()->unsigned()->change();
        });
    }
};
