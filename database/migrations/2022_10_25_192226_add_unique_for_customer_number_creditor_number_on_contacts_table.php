<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table): void {
            $table->dropIndex('contacts_customer_number_index');
            $table->unique(['customer_number', 'client_id']);
            $table->unique(['creditor_number', 'client_id']);
        });
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table): void {
            $table->dropUnique('contacts_customer_number_client_id_unique');
            $table->dropUnique('contacts_creditor_number_client_id_unique');
            $table->index('customer_number');
        });
    }
};
