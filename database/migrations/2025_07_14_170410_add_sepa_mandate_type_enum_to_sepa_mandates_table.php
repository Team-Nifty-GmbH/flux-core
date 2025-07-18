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
            $table->string('sepa_mandate_type_enum')
                ->nullable()
                ->after('contact_bank_connection_id');
        });

        $this->migrateExistingData();

        Schema::table('sepa_mandates', function (Blueprint $table): void {
            $table->string('sepa_mandate_type_enum')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('sepa_mandates', function (Blueprint $table): void {
            $table->dropColumn('sepa_mandate_type_enum');
        });
    }

    private function migrateExistingData(): void
    {
        DB::table('sepa_mandates')
            ->whereNull('sepa_mandate_type_enum')
            ->update(['sepa_mandate_type_enum' => 'BASIC']);
    }
};
