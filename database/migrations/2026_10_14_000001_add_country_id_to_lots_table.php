<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('lots', function (Blueprint $table): void {
            $table->foreignId('country_id')
                ->nullable()
                ->after('uuid')
                ->comment('Country of origin of this lot, the same product can come from another country with the next delivery.')
                ->constrained('countries')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('lots', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('country_id');
        });
    }
};
