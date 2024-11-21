<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('email_templates')) {
            return;
        }

        Schema::create('email_templates', function (Blueprint $table) {
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
            $table->timestamp('created_at')->nullable()->comment('A timestamp reflecting the time of record-creation.');
            $table->unsignedBigInteger('created_by')->nullable()->index('email_templates_created_by_foreign')->comment('A unique identifier number for the table users of the user that created this record.');
            $table->timestamp('updated_at')->nullable()->comment('A timestamp reflecting the time of the last change for this record.');
            $table->unsignedBigInteger('updated_by')->nullable()->index('email_templates_updated_by_foreign')->comment('A unique identifier number for the table users of the user that changed this record last.');
            $table->softDeletes()->comment('A timestamp reflecting the time of record-deletion.');
            $table->unsignedBigInteger('deleted_by')->nullable()->index('email_templates_deleted_by_foreign')->comment('A unique identifier number for the table users of the user that deleted this record.');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
