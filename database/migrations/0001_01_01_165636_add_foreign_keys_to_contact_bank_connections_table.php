<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('contact_bank_connections', function (Blueprint $table) {
            $table->foreign(['contact_id'])->references(['id'])->on('contacts')->onUpdate('no action')->onDelete('no action');
        });
    }

    public function down(): void
    {
        Schema::table('contact_bank_connections', function (Blueprint $table) {
            $table->dropForeign('contact_bank_connections_contact_id_foreign');
        });
    }
};
