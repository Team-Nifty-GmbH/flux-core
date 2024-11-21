<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('communications')) {
            return;
        }

        Schema::create('communications', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36);
            $table->unsignedBigInteger('mail_account_id')->nullable()->index('communications_mail_account_id_foreign');
            $table->unsignedBigInteger('mail_folder_id')->nullable()->index('communications_mail_folder_id_foreign');
            $table->string('message_id')->nullable()->index('mail_messages_message_id_index');
            $table->integer('message_uid')->nullable()->index('mail_messages_message_uid_index');
            $table->json('from')->nullable();
            $table->json('to')->nullable();
            $table->json('cc')->nullable();
            $table->json('bcc')->nullable();
            $table->string('communication_type_enum');
            $table->dateTime('date')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('ended_at')->nullable();
            $table->unsignedBigInteger('total_time_ms')->default(0);
            $table->string('subject')->nullable();
            $table->longText('text_body')->nullable();
            $table->longText('html_body')->nullable();
            $table->boolean('is_seen')->default(false);
            $table->timestamp('created_at')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('updated_by')->nullable();
            $table->softDeletes();
            $table->string('deleted_by')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('communications');
    }
};
