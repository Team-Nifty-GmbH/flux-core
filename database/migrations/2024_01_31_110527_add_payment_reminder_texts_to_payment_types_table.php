<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('payment_types', function (Blueprint $table) {
            $table->text('payment_reminder_text')->nullable()->after('payment_discount_percentage');
            $table->text('payment_reminder_email_text')->nullable()->after('payment_reminder_text');
        });
    }

    public function down(): void
    {
        Schema::table('payment_types', function (Blueprint $table) {
            $table->dropColumn(['payment_reminder_text', 'payment_reminder_email_text']);
        });
    }
};
