<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mail_messages', function (Blueprint $table) {
            $table->id();
            $table->uuid();
            $table->foreignId('mail_account_id')->constrained()->onDelete('cascade');
            $table->foreignId('mail_folder_id')->constrained()->onDelete('cascade');
            $table->string('message_id')->nullable()->index(); // $msg->getMessageId()->toArray()
            $table->integer('message_uid')->nullable()->index(); // $msg->getMessageNo()
            $table->json('from')->nullable(); // $msg->getFrom()->toArray()
            $table->json('to')->nullable(); // $msg->getTo()->toArray()
            $table->json('cc')->nullable(); // $msg->getCc()->toArray()
            $table->json('bcc')->nullable(); // $msg->getBcc()->toArray()
            $table->timestamp('date')->nullable(); // $msg->getDate()->toDate()
            $table->string('subject')->nullable(); // $msg->getSubject()->toString()
            $table->longText('text_body')->nullable(); // $msg->getTextBody();
            $table->longText('html_body')->nullable(); // $msg->getHtmlBody();
            $table->boolean('is_seen')->default(false); // $msg->isSeen()
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_messages');
    }
};
