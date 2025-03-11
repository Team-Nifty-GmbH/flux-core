<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentGenerationSettingsTable extends Migration
{
    public function up(): void
    {
        Schema::create('document_generation_settings', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('document_type_id');
            $table->unsignedBigInteger('order_type_id');
            $table->boolean('is_active')->default(false);
            $table->boolean('is_generation_preset')->default(false);
            $table->boolean('is_generation_forced')->default(false);
            $table->boolean('is_print_preset')->default(false);
            $table->boolean('is_print_forced')->default(false);
            $table->boolean('is_email_preset')->default(false);
            $table->boolean('is_email_forced')->default(false);
            $table->timestamp('created_at')->nullable()
                ->comment('A timestamp reflecting the time of record-creation.');
            $table->unsignedBigInteger('created_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that created this record.');
            $table->timestamp('updated_at')->nullable()
                ->comment('A timestamp reflecting the time of the last change for this record.');
            $table->unsignedBigInteger('updated_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that changed this record last.');
            $table->timestamp('deleted_at')->nullable()
                ->comment('A timestamp reflecting the time of record-deletion.');
            $table->unsignedBigInteger('deleted_by')->nullable()
                ->comment('A unique identifier number for the table users of the user that deleted this record.');

            $table->foreign('client_id')->references('id')->on('clients');
            $table->foreign('document_type_id')->references('id')->on('document_types');
            $table->foreign('order_type_id')->references('id')->on('order_types');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('deleted_by')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_generation_settings');
    }
}
