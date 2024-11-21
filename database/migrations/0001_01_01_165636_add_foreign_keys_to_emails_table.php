<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('emails', function (Blueprint $table) {
            $table->foreign(['created_by'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['deleted_by'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['email_template_id'])->references(['id'])->on('email_templates')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['updated_by'])->references(['id'])->on('users')->onUpdate('no action')->onDelete('no action');
        });
    }

    public function down(): void
    {
        Schema::table('emails', function (Blueprint $table) {
            $table->dropForeign('emails_created_by_foreign');
            $table->dropForeign('emails_deleted_by_foreign');
            $table->dropForeign('emails_email_template_id_foreign');
            $table->dropForeign('emails_updated_by_foreign');
        });
    }
};
