<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mail_folders')) {
            return;
        }

        Schema::create('mail_folders', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36);
            $table->unsignedBigInteger('mail_account_id')->index('mail_folders_mail_account_id_foreign');
            $table->unsignedBigInteger('parent_id')->nullable()->index('mail_folders_parent_id_foreign');
            $table->string('name');
            $table->string('slug');
            $table->boolean('can_create_ticket')->default(false);
            $table->boolean('can_create_purchase_invoice')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_folders');
    }
};
