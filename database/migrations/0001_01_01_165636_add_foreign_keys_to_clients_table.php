<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->foreign(['commission_credit_note_order_type_id'])->references(['id'])->on('order_types')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['country_id'])->references(['id'])->on('countries')->onUpdate('no action')->onDelete('no action');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign('clients_commission_credit_note_order_type_id_foreign');
            $table->dropForeign('clients_country_id_foreign');
        });
    }
};
