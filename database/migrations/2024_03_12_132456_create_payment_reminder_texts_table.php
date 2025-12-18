<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('payment_reminder_texts', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->foreignId('email_template_id')
                ->nullable()
                ->constrained('email_templates')
                ->nullOnDelete();
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
