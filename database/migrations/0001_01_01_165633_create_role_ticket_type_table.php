<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('role_ticket_type')) {
            return;
        }

        Schema::create('role_ticket_type', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('role_id')->index('role_ticket_type_role_id_foreign');
            $table->unsignedBigInteger('ticket_type_id')->index('role_ticket_type_ticket_type_id_foreign');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_ticket_type');
    }
};
