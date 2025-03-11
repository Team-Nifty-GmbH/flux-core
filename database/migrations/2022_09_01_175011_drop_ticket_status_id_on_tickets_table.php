<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table): void {
            $table->dropForeign('tickets_ticket_status_id_foreign');
            $table->dropColumn('ticket_status_id');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table): void {
            $table->unsignedBigInteger('ticket_status_id')->after('address_id');

            $table->foreign('ticket_status_id')->references('id')->on('ticket_statuses');
        });
    }
};
