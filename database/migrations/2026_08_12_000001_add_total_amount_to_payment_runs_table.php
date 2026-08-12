<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('payment_runs', function (Blueprint $table): void {
            $table->decimal('total_amount', 40, 10)
                ->default(0)
                ->after('payment_run_type_enum');
        });

        DB::table('payment_runs')->update([
            'total_amount' => DB::raw(
                '(select coalesce(sum(`amount`), 0) from `order_payment_run` '
                . 'where `order_payment_run`.`payment_run_id` = `payment_runs`.`id`)'
            ),
        ]);
    }

    public function down(): void
    {
        Schema::table('payment_runs', function (Blueprint $table): void {
            $table->dropColumn('total_amount');
        });
    }
};
