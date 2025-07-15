<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_runs', function (Blueprint $table) {
            $table->string('sepa_mandate_type_enum')
                ->nullable()
                ->after('payment_run_type_enum');
        });
    }

    public function down(): void
    {
        Schema::table('payment_runs', function (Blueprint $table) {
            $table->dropColumn('sepa_mandate_type_enum');
        });
    }
};
