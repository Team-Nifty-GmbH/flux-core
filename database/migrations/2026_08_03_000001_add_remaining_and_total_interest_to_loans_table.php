<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('loans', function (Blueprint $table): void {
            $table->decimal('remaining', 40, 10)->nullable()->after('installment_amount');
            $table->decimal('total_interest', 40, 10)->nullable()->after('remaining');

            $table->decimal('progress', 11, 10)->default(0)->after('total_interest');
        });

        DB::statement(<<<'SQL'
            UPDATE loans
            SET remaining = (
                    SELECT COALESCE(SUM(principal_amount), 0)
                    FROM loan_installments
                    WHERE loan_installments.loan_id = loans.id
                      AND loan_installments.is_paid = 0
                      AND loan_installments.deleted_at IS NULL
                ),
                total_interest = (
                    SELECT COALESCE(SUM(interest_amount), 0)
                    FROM loan_installments
                    WHERE loan_installments.loan_id = loans.id
                      AND loan_installments.deleted_at IS NULL
                )
        SQL);

        DB::statement('UPDATE loans SET progress = (amount - COALESCE(remaining, 0)) / amount WHERE amount > 0');
    }

    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table): void {
            $table->dropColumn(['remaining', 'total_interest', 'progress']);
        });
    }
};
