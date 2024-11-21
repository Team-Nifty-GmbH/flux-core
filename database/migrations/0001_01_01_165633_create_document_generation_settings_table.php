<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('document_generation_settings')) {
            return;
        }

        Schema::create('document_generation_settings', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36);
            $table->unsignedBigInteger('client_id')->index('document_generation_settings_client_id_foreign');
            $table->unsignedBigInteger('document_type_id')->index('document_generation_settings_document_type_id_foreign');
            $table->unsignedBigInteger('order_type_id')->index('document_generation_settings_order_type_id_foreign');
            $table->boolean('is_active')->default(false);
            $table->boolean('is_generation_preset')->default(false);
            $table->boolean('is_generation_forced')->default(false);
            $table->boolean('is_print_preset')->default(false);
            $table->boolean('is_print_forced')->default(false);
            $table->boolean('is_email_preset')->default(false);
            $table->boolean('is_email_forced')->default(false);
            $table->timestamp('created_at')->nullable()->comment('A timestamp reflecting the time of record-creation.');
            $table->unsignedBigInteger('created_by')->nullable()->index('document_generation_settings_created_by_foreign')->comment('A unique identifier number for the table users of the user that created this record.');
            $table->timestamp('updated_at')->nullable()->comment('A timestamp reflecting the time of the last change for this record.');
            $table->unsignedBigInteger('updated_by')->nullable()->index('document_generation_settings_updated_by_foreign')->comment('A unique identifier number for the table users of the user that changed this record last.');
            $table->softDeletes()->comment('A timestamp reflecting the time of record-deletion.');
            $table->unsignedBigInteger('deleted_by')->nullable()->index('document_generation_settings_deleted_by_foreign')->comment('A unique identifier number for the table users of the user that deleted this record.');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_generation_settings');
    }
};
