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

        Schema::create('document_types', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36);
            $table->unsignedBigInteger('client_id')->index('document_types_client_id_foreign');
            $table->json('name');
            $table->json('description')->nullable();
            $table->json('additional_header')->nullable();
            $table->json('additional_footer')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->nullable()->comment('A timestamp reflecting the time of record-creation.');
            $table->unsignedBigInteger('created_by')->nullable()->index('document_types_created_by_foreign')->comment('A unique identifier number for the table users of the user that created this record.');
            $table->timestamp('updated_at')->nullable()->comment('A timestamp reflecting the time of the last change for this record.');
            $table->unsignedBigInteger('updated_by')->nullable()->index('document_types_updated_by_foreign')->comment('A unique identifier number for the table users of the user that changed this record last.');
            $table->softDeletes()->comment('A timestamp reflecting the time of record-deletion.');
            $table->unsignedBigInteger('deleted_by')->nullable()->index('document_types_deleted_by_foreign')->comment('A unique identifier number for the table users of the user that deleted this record.');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_types');
    }
};
