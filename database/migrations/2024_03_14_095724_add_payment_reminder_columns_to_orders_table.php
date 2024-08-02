<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedInteger('payment_reminder_current_level')->nullable()->after('payment_reminder_days_3');
            $table->date('payment_reminder_next_date')->nullable()->after('payment_reminder_current_level');
        });

        DB::statement('UPDATE orders
            SET payment_reminder_next_date = DATE_ADD(invoice_date, INTERVAL payment_reminder_days_1 DAY)
            WHERE invoice_number is not null'
        );
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_reminder_next_date', 'payment_reminder_current_level']);
        });
    }
};
