<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('loans', function (Blueprint $table): void {
            // no allowance at all, an unlimited one, or a yearly cap: the flag
            // says whether extra repayments are possible, the two amounts cap
            // them per calendar year when set
            $table->boolean('allows_extra_repayments')
                ->default(true)
                ->after('grace_period_installments');
            $table->decimal('extra_repayment_allowance_percentage', 40, 10)
                ->nullable()
                ->after('allows_extra_repayments');
            $table->decimal('extra_repayment_allowance_amount', 40, 10)
                ->nullable()
                ->after('extra_repayment_allowance_percentage');
        });
    }

    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table): void {
            $table->dropColumn([
                'allows_extra_repayments',
                'extra_repayment_allowance_percentage',
                'extra_repayment_allowance_amount',
            ]);
        });
    }
};
