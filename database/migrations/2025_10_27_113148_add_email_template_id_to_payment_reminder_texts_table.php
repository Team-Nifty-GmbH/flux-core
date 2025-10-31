<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('payment_reminder_texts', function (Blueprint $table): void {
            $table->foreignId('email_template_id')
                ->nullable()
                ->after('uuid')
                ->constrained('email_templates')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('payment_reminder_texts', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('email_template_id');
        });
    }
};
