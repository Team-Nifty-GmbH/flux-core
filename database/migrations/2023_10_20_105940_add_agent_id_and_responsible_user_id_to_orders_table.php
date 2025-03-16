<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->unsignedBigInteger('agent_id')->nullable()->after('client_id');
            $table->unsignedBigInteger('responsible_user_id')->nullable()->after('payment_type_id');

            $table->foreign('agent_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('responsible_user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropForeign(['agent_id']);
            $table->dropForeign(['responsible_user_id']);
            $table->dropColumn(['agent_id', 'responsible_user_id']);
        });
    }
};
