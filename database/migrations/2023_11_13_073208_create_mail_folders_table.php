<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('mail_folders', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);
            $table->foreignId('mail_account_id')->constrained('mail_accounts')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('mail_folders')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->boolean('can_create_lead')->default(false);
            $table->boolean('can_create_purchase_invoice')->default(false);
            $table->boolean('can_create_ticket')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_folders');
    }
};
