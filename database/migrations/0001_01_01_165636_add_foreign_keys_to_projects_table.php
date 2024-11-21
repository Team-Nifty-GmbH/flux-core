<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->foreign(['client_id'])->references(['id'])->on('clients')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['contact_id'])->references(['id'])->on('contacts')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['order_id'])->references(['id'])->on('orders')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['parent_id'])->references(['id'])->on('projects')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['responsible_user_id'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign('projects_client_id_foreign');
            $table->dropForeign('projects_contact_id_foreign');
            $table->dropForeign('projects_order_id_foreign');
            $table->dropForeign('projects_parent_id_foreign');
            $table->dropForeign('projects_responsible_user_id_foreign');
        });
    }
};
