<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('mail_messages', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);
            $table->foreignId('mail_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mail_folder_id')->constrained()->cascadeOnDelete();
            $table->string('message_id')->nullable()->index();
            $table->integer('message_uid')->nullable()->index();
            $table->json('from')->nullable();
            $table->json('to')->nullable();
            $table->json('cc')->nullable();
            $table->json('bcc')->nullable();
            $table->dateTime('date')->nullable();
            $table->string('subject')->nullable();
            $table->longText('text_body')->nullable();
            $table->longText('html_body')->nullable();
            $table->boolean('is_seen')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_messages');
    }
};
