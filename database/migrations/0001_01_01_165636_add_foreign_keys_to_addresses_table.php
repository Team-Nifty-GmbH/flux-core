<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->foreign(['client_id'])->references(['id'])->on('clients')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['contact_id'])->references(['id'])->on('contacts')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['country_id'])->references(['id'])->on('countries')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['language_id'])->references(['id'])->on('languages')->onUpdate('no action')->onDelete('no action');
        });
    }

    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropForeign('addresses_client_id_foreign');
            $table->dropForeign('addresses_contact_id_foreign');
            $table->dropForeign('addresses_country_id_foreign');
            $table->dropForeign('addresses_language_id_foreign');
        });
    }
};
