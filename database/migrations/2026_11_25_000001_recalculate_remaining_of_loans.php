<?php

use FluxErp\Models\Loan;
use Illuminate\Database\Migrations\Migration;

return new class() extends Migration
{
    public function up(): void
    {
        // Partial repayments now lower the remaining principal, loans stored before keep the old figure until recalculated.
        Loan::query()
            ->withoutGlobalScopes()
            ->whereNull('deleted_at')
            ->each(fn (Loan $loan) => $loan->calculateRemaining()->calculateProgress()->saveQuietly());
    }

    public function down(): void
    {
        //
    }
};
