<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bank_connections', function (Blueprint $table) {
            $table->rename('contact_bank_connections');
        });
        Schema::table('contact_bank_connections', function (Blueprint $table) {
            $table->unsignedBigInteger('contact_id')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('contact_bank_connections', function (Blueprint $table) {
            $table->unsignedBigInteger('contact_id')->nullable()->change();
            $table->rename('bank_connections');
        });
    }
};
