<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('payment_notices')) {
            return;
        }

        Schema::create('payment_notices', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36);
            $table->unsignedBigInteger('client_id')->index('payment_notices_client_id_foreign');
            $table->unsignedBigInteger('payment_type_id')->index('payment_notices_payment_type_id_foreign');
            $table->unsignedBigInteger('document_type_id')->index('payment_notices_document_type_id_foreign');
            $table->json('payment_notice');
            $table->timestamp('created_at')->nullable()->comment('A timestamp reflecting the time of record-creation.');
            $table->unsignedBigInteger('created_by')->nullable()->index('payment_notices_created_by_foreign')->comment('A unique identifier number for the table users of the user that created this record.');
            $table->timestamp('updated_at')->nullable()->comment('A timestamp reflecting the time of the last change for this record.');
            $table->unsignedBigInteger('updated_by')->nullable()->index('payment_notices_updated_by_foreign')->comment('A unique identifier number for the table users of the user that changed this record last.');
            $table->softDeletes()->comment('A timestamp reflecting the time of record-deletion.');
            $table->unsignedBigInteger('deleted_by')->nullable()->index('payment_notices_deleted_by_foreign')->comment('A unique identifier number for the table users of the user that deleted this record.');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_notices');
    }
};
