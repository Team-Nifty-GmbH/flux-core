<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::rename('contact_bank_connections', 'bank_connections');

        Schema::table('bank_connections', function (Blueprint $table): void {
            $table->unsignedBigInteger('contact_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('bank_connections', 'contact_bank_connections');

        DB::statement('DELETE FROM contact_bank_connections WHERE contact_id IS NULL');

        Schema::table('contact_bank_connections', function (Blueprint $table): void {
            $table->unsignedBigInteger('contact_id')->nullable(false)->change();
        });
    }
};
