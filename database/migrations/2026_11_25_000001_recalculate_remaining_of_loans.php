<?php

use FluxErp\Models\Loan;
use Illuminate\Database\Migrations\Migration;

return new class() extends Migration
{
    public function up(): void
    {
        resolve_static(Loan::class, 'query')
            ->each(fn (Loan $loan) => $loan->calculateRemaining()->calculateProgress()->saveQuietly());
    }

    public function down(): void
    {
        //
    }
};
