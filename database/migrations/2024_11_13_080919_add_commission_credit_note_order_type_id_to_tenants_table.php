<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('tenants', 'commission_credit_note_order_type_id')) {
            return;
        }

        Schema::table('tenants', function (Blueprint $table): void {
            $table->foreignId('commission_credit_note_order_type_id')
                ->nullable()
                ->after('uuid')
                ->constrained('order_types')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('commission_credit_note_order_type_id');
        });
    }
};
