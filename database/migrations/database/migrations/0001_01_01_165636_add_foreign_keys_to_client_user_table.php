<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('client_user', function (Blueprint $table) {
            $table->foreign(['client_id'])->references(['id'])->on('clients')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['user_id'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('no action');
        });
    }

    public function down(): void
    {
        Schema::table('client_user', function (Blueprint $table) {
            $table->dropForeign('client_user_client_id_foreign');
            $table->dropForeign('client_user_user_id_foreign');
        });
    }
};
