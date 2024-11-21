<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_runs', function (Blueprint $table) {
            $table->foreign(['bank_connection_id'])->references(['id'])->on('bank_connections')->onUpdate('no action')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('payment_runs', function (Blueprint $table) {
            $table->dropForeign('payment_runs_bank_connection_id_foreign');
        });
    }
};
