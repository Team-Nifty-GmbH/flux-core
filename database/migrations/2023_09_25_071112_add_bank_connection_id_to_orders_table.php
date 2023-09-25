<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('approval_user_id')
                ->after('uuid')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');
            $table->foreignId('bank_connection_id')
                ->after('contact_id')
                ->nullable()
                ->constrained('bank_connections')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['approval_user_id', 'bank_connection_id']);
            $table->dropColumn(['approval_user_id', 'bank_connection_id']);
        });
    }
};
