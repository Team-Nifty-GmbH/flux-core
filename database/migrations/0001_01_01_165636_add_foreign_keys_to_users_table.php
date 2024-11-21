<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreign(['contact_id'])->references(['id'])->on('contacts')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['currency_id'])->references(['id'])->on('currencies')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['language_id'])->references(['id'])->on('languages')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['parent_id'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign('users_contact_id_foreign');
            $table->dropForeign('users_currency_id_foreign');
            $table->dropForeign('users_language_id_foreign');
            $table->dropForeign('users_parent_id_foreign');
        });
    }
};
