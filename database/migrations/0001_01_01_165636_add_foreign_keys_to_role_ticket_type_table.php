<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('role_ticket_type', function (Blueprint $table) {
            $table->foreign(['role_id'])->references(['id'])->on('roles')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['ticket_type_id'])->references(['id'])->on('ticket_types')->onUpdate('no action')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('role_ticket_type', function (Blueprint $table) {
            $table->dropForeign('role_ticket_type_role_id_foreign');
            $table->dropForeign('role_ticket_type_ticket_type_id_foreign');
        });
    }
};
