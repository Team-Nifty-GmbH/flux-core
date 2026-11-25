<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('loans', function (Blueprint $table): void {
            $table->decimal('extra_repayment_allowance_percentage', 11, 10)
                ->nullable()
                ->after('progress');
            $table->decimal('extra_repayment_allowance_amount', 40, 10)
                ->nullable()
                ->after('extra_repayment_allowance_percentage');
            $table->boolean('allows_extra_repayments')
                ->default(true)
                ->after('ends_at');
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
