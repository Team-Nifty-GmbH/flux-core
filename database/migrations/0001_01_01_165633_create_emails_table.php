<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('emails')) {
            return;
        }

        Schema::create('emails', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36);
            $table->unsignedBigInteger('email_template_id')->index('emails_email_template_id_foreign');
            $table->string('model_type')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->string('from');
            $table->string('from_alias')->nullable();
            $table->json('to');
            $table->json('cc')->nullable();
            $table->json('bcc')->nullable();
            $table->string('subject')->nullable();
            $table->text('message')->nullable();
            $table->string('view');
            $table->json('view_data')->nullable();
            $table->timestamp('created_at')->nullable()->comment('A timestamp reflecting the time of record-creation.');
            $table->unsignedBigInteger('created_by')->nullable()->index('emails_created_by_foreign')->comment('A unique identifier number for the table users of the user that created this record.');
            $table->timestamp('updated_at')->nullable()->comment('A timestamp reflecting the time of the last change for this record.');
            $table->unsignedBigInteger('updated_by')->nullable()->index('emails_updated_by_foreign')->comment('A unique identifier number for the table users of the user that changed this record last.');
            $table->softDeletes()->comment('A timestamp reflecting the time of record-deletion.');
            $table->unsignedBigInteger('deleted_by')->nullable()->index('emails_deleted_by_foreign')->comment('A unique identifier number for the table users of the user that deleted this record.');

            $table->index(['model_type', 'model_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emails');
    }
};
