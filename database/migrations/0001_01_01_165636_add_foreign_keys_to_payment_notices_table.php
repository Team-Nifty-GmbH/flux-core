<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_notices', function (Blueprint $table) {
            $table->foreign(['client_id'])->references(['id'])->on('clients')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['created_by'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['deleted_by'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['document_type_id'])->references(['id'])->on('document_types')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['payment_type_id'])->references(['id'])->on('payment_types')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['updated_by'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('no action');
        });
    }

    public function down(): void
    {
        Schema::table('payment_notices', function (Blueprint $table) {
            $table->dropForeign('payment_notices_client_id_foreign');
            $table->dropForeign('payment_notices_created_by_foreign');
            $table->dropForeign('payment_notices_deleted_by_foreign');
            $table->dropForeign('payment_notices_document_type_id_foreign');
            $table->dropForeign('payment_notices_payment_type_id_foreign');
            $table->dropForeign('payment_notices_updated_by_foreign');
        });
    }
};
