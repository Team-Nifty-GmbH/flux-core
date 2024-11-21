<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('payment_reminder_texts')) {
            return;
        }

        Schema::create('payment_reminder_texts', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->json('mail_to')->nullable();
            $table->json('mail_cc')->nullable();
            $table->string('mail_subject')->nullable();
            $table->text('mail_body')->nullable();
            $table->string('reminder_subject')->nullable();
            $table->text('reminder_body');
            $table->unsignedInteger('reminder_level')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_reminder_texts');
    }
};
