<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('custom_events');
        Schema::dropIfExists('document_generation_settings');
        Schema::dropIfExists('payment_notices');
        Schema::dropIfExists('emails');
        Schema::dropIfExists('email_templates');
    }

    public function down(): void
    {
        Schema::create('email_templates', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);
            $table->string('name');
            $table->string('from')->nullable();
            $table->string('from_alias')->nullable();
            $table->json('to')->nullable();
            $table->json('cc')->nullable();
            $table->json('bcc')->nullable();
            $table->string('subject')->nullable();
            $table->text('message')->nullable();
            $table->string('view');
            $table->json('view_data')->nullable();

            $table->boolean('can_overwrite_message')->default(true);
            $table->boolean('can_overwrite_receiver')->default(true);
            $table->boolean('can_overwrite_sender')->default(true);
            $table->boolean('can_overwrite_subject')->default(true);
            $table->boolean('can_overwrite_view')->default(false);
            $table->boolean('should_prohibit_release')->default(false);

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

            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('deleted_by')->references('id')->on('users');
        });

        Schema::create('emails', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);
            $table->unsignedBigInteger('email_template_id');
            $table->nullableMorphs('model');
            $table->string('from');
            $table->string('from_alias')->nullable();
            $table->json('to');
            $table->json('cc')->nullable();
            $table->json('bcc')->nullable();
            $table->string('subject')->nullable();
            $table->text('message')->nullable();
            $table->string('view');
            $table->json('view_data')->nullable();

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

            $table->foreign('email_template_id')->references('id')->on('email_templates');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('deleted_by')->references('id')->on('users');
        });

        Schema::create('payment_notices', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('payment_type_id');
            $table->unsignedBigInteger('document_type_id');
            $table->text('payment_notice')->nullable();
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
            $table->foreign('payment_type_id')->references('id')->on('payment_types');
            $table->foreign('document_type_id')->references('id')->on('document_types');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('deleted_by')->references('id')->on('users');
        });

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

        Schema::create('custom_events', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique()->index();
            $table->nullableMorphs('model');
            $table->timestamps();
        });
    }
};
