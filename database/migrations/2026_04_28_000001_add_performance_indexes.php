<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('addresses', function (Blueprint $table): void {
            $table->index('email_primary');
            $table->index('deleted_at');
        });

        Schema::table('bank_connections', function (Blueprint $table): void {
            $table->index('deleted_at');
        });

        Schema::table('comments', function (Blueprint $table): void {
            $table->index('deleted_at');
        });

        Schema::table('communications', function (Blueprint $table): void {
            $table->index('communication_type_enum');
            $table->index('date');
            $table->index('deleted_at');
        });

        Schema::table('contacts', function (Blueprint $table): void {
            $table->index('deleted_at');
        });

        Schema::table('employees', function (Blueprint $table): void {
            $table->index('deleted_at');
        });

        Schema::table('leads', function (Blueprint $table): void {
            $table->index('deleted_at');
        });

        Schema::table('notifications', function (Blueprint $table): void {
            $table->index('read_at');
        });

        Schema::table('order_positions', function (Blueprint $table): void {
            $table->index('deleted_at');
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->index('state');
            $table->index('payment_state');
            $table->index('payment_reminder_next_date');
            $table->index('order_date');
            $table->index('invoice_date');
            $table->index('invoice_number');
            $table->index('system_delivery_date');
            $table->index('system_delivery_date_end');
            $table->index('deleted_at');
        });

        Schema::table('payment_reminders', function (Blueprint $table): void {
            $table->index('deleted_at');
        });

        Schema::table('purchase_invoices', function (Blueprint $table): void {
            $table->index('invoice_date');
            $table->index('deleted_at');
        });

        Schema::table('schedules', function (Blueprint $table): void {
            $table->index('due_at');
            $table->index('ends_at');
        });

        Schema::table('tickets', function (Blueprint $table): void {
            $table->index('state');
            $table->index('resolved_at');
            $table->index('deleted_at');
        });

        Schema::table('transactions', function (Blueprint $table): void {
            $table->index('booking_date');
            $table->index('deleted_at');
        });

        Schema::table('work_times', function (Blueprint $table): void {
            $table->index('started_at');
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::table('work_times', function (Blueprint $table): void {
            $table->dropIndex(['deleted_at']);
            $table->dropIndex(['started_at']);
        });

        Schema::table('transactions', function (Blueprint $table): void {
            $table->dropIndex(['deleted_at']);
            $table->dropIndex(['booking_date']);
        });

        Schema::table('tickets', function (Blueprint $table): void {
            $table->dropIndex(['deleted_at']);
            $table->dropIndex(['resolved_at']);
            $table->dropIndex(['state']);
        });

        Schema::table('schedules', function (Blueprint $table): void {
            $table->dropIndex(['ends_at']);
            $table->dropIndex(['due_at']);
        });

        Schema::table('purchase_invoices', function (Blueprint $table): void {
            $table->dropIndex(['deleted_at']);
            $table->dropIndex(['invoice_date']);
        });

        Schema::table('payment_reminders', function (Blueprint $table): void {
            $table->dropIndex(['deleted_at']);
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropIndex(['deleted_at']);
            $table->dropIndex(['system_delivery_date_end']);
            $table->dropIndex(['system_delivery_date']);
            $table->dropIndex(['invoice_number']);
            $table->dropIndex(['invoice_date']);
            $table->dropIndex(['order_date']);
            $table->dropIndex(['payment_reminder_next_date']);
            $table->dropIndex(['payment_state']);
            $table->dropIndex(['state']);
        });

        Schema::table('order_positions', function (Blueprint $table): void {
            $table->dropIndex(['deleted_at']);
        });

        Schema::table('notifications', function (Blueprint $table): void {
            $table->dropIndex(['read_at']);
        });

        Schema::table('leads', function (Blueprint $table): void {
            $table->dropIndex(['deleted_at']);
        });

        Schema::table('employees', function (Blueprint $table): void {
            $table->dropIndex(['deleted_at']);
        });

        Schema::table('contacts', function (Blueprint $table): void {
            $table->dropIndex(['deleted_at']);
        });

        Schema::table('communications', function (Blueprint $table): void {
            $table->dropIndex(['deleted_at']);
            $table->dropIndex(['date']);
            $table->dropIndex(['communication_type_enum']);
        });

        Schema::table('comments', function (Blueprint $table): void {
            $table->dropIndex(['deleted_at']);
        });

        Schema::table('bank_connections', function (Blueprint $table): void {
            $table->dropIndex(['deleted_at']);
        });

        Schema::table('addresses', function (Blueprint $table): void {
            $table->dropIndex(['deleted_at']);
            $table->dropIndex(['email_primary']);
        });
    }
};
