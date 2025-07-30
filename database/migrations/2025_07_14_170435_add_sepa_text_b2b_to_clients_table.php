<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table): void {
            $table->text('sepa_text_b2b')
                ->nullable()
                ->after('sepa_text');

            $table->renameColumn('sepa_text', 'sepa_text_basic');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table): void {
            $table->renameColumn('sepa_text_basic', 'sepa_text');
            $table->dropColumn('sepa_text_b2b');
        });
    }
};
