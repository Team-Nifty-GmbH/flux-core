<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::rename('bank_connections', 'contact_bank_connections');

        DB::table('contact_bank_connections')
            ->whereNull('contact_id')
            ->delete();

        Schema::table('contact_bank_connections', function (Blueprint $table) {
            $table->unsignedBigInteger('contact_id')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::rename('contact_bank_connections', 'bank_connections');

        Schema::table('contact_bank_connections', function (Blueprint $table) {
            $table->unsignedBigInteger('contact_id')->nullable()->change();
        });
    }
};
