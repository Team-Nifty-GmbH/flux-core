<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mailables', function (Blueprint $table) {
            $table->id();
            $table->morphs('mailable');
            $table->foreignId('mail_message_id')->constrained('mail_messages')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['mailable_id', 'mailable_type', 'mail_message_id'], 'mailables_ids_type_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mailables');
    }
};
