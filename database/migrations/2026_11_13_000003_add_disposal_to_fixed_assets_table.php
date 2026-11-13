<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('fixed_assets', function (Blueprint $table): void {
            $table->foreignId('disposal_ledger_account_id')
                ->nullable()
                ->after('depreciation_ledger_account_id')
                ->constrained('ledger_accounts')
                ->nullOnDelete();
            $table->date('disposed_at')
                ->nullable()
                ->after('externally_depreciated_until');
        });
    }

    public function down(): void
    {
        Schema::table('fixed_assets', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('disposal_ledger_account_id');
            $table->dropColumn('disposed_at');
        });
    }
};
