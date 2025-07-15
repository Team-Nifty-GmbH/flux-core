<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('sepa_mandates', function (Blueprint $table): void {
            $table->string('type')
                ->nullable()
                ->after('contact_bank_connection_id');
        });

        $this->migrateExistingData();

        Schema::table('sepa_mandates', function (Blueprint $table): void {
            $table->string('type')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('sepa_mandates', function (Blueprint $table): void {
            $table->dropColumn('type');
        });
    }

    private function migrateExistingData(): void
    {
        DB::table('sepa_mandates')
            ->whereNull('type')
            ->update(['type' => 'B2C']);
    }
};
