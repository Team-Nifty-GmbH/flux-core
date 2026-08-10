<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('loans', function (Blueprint $table): void {
            $table->string('installment_interval_enum')
                ->default('monthly')
                ->after('number_of_installments');
            $table->unsignedInteger('grace_period_installments')
                ->default(0)
                ->after('installment_interval_enum');
        });
    }

    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table): void {
            $table->dropColumn(['installment_interval_enum', 'grace_period_installments']);
        });
    }
};
