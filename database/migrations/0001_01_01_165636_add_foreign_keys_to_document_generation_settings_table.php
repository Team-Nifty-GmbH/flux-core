<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_generation_settings', function (Blueprint $table) {
            $table->foreign(['client_id'])->references(['id'])->on('clients')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['created_by'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['deleted_by'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['document_type_id'])->references(['id'])->on('document_types')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['order_type_id'])->references(['id'])->on('order_types')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['updated_by'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('no action');
        });
    }

    public function down(): void
    {
        Schema::table('document_generation_settings', function (Blueprint $table) {
            $table->dropForeign('document_generation_settings_client_id_foreign');
            $table->dropForeign('document_generation_settings_created_by_foreign');
            $table->dropForeign('document_generation_settings_deleted_by_foreign');
            $table->dropForeign('document_generation_settings_document_type_id_foreign');
            $table->dropForeign('document_generation_settings_order_type_id_foreign');
            $table->dropForeign('document_generation_settings_updated_by_foreign');
        });
    }
};
