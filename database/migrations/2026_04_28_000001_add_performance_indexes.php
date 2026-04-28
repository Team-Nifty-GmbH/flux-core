<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->index('invoice_date');
            $table->index('payment_reminder_next_date');
            $table->index('order_date');
            $table->index('deleted_at');
            $table->index(['payment_state', 'state']);
            $table->index(['system_delivery_date', 'system_delivery_date_end']);
        });

        Schema::table('transactions', function (Blueprint $table): void {
            $table->index('booking_date');
            $table->index('deleted_at');
            $table->index(['is_ignored', 'booking_date']);
        });

        Schema::table('work_times', function (Blueprint $table): void {
            $table->index('deleted_at');
            $table->index(['user_id', 'started_at']);
            $table->index(['employee_id', 'started_at']);
            $table->index(['is_locked', 'is_daily_work_time']);
        });

        Schema::table('schedules', function (Blueprint $table): void {
            $table->index(['is_active', 'due_at', 'ends_at']);
        });

        Schema::table('payment_reminders', function (Blueprint $table): void {
            $table->index('deleted_at');
            $table->index(['order_id', 'reminder_level']);
        });

        Schema::table('tickets', function (Blueprint $table): void {
            $table->index('resolved_at');
            $table->index('deleted_at');
            $table->index(['state', 'created_at']);
        });

        Schema::table('communications', function (Blueprint $table): void {
            $table->index('deleted_at');
            $table->index(['mail_folder_id', 'date']);
            $table->index(['mail_account_id', 'communication_type_enum']);
        });

        Schema::table('addresses', function (Blueprint $table): void {
            $table->index('email_primary');
            $table->index('deleted_at');
        });

        Schema::table('contacts', function (Blueprint $table): void {
            $table->index('deleted_at');
        });

        Schema::table('leads', function (Blueprint $table): void {
            $table->index('deleted_at');
        });

        Schema::table('order_positions', function (Blueprint $table): void {
            $table->index('deleted_at');
            $table->index(['order_id', 'sort_number']);
        });

        Schema::table('comments', function (Blueprint $table): void {
            $table->index('deleted_at');
        });

        Schema::table('event_subscriptions', function (Blueprint $table): void {
            $table->index(['subscribable_type', 'subscribable_id', 'event']);
        });

        Schema::table('notifications', function (Blueprint $table): void {
            $table->index(['notifiable_type', 'notifiable_id', 'read_at']);
        });

        Schema::table('employees', function (Blueprint $table): void {
            $table->index('is_active');
            $table->index('deleted_at');
        });

        Schema::table('purchase_invoices', function (Blueprint $table): void {
            $table->index('invoice_date');
            $table->index('deleted_at');
        });

        Schema::table('bank_connections', function (Blueprint $table): void {
            $table->index('is_active');
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropIndex(['invoice_date']);
            $table->dropIndex(['payment_reminder_next_date']);
            $table->dropIndex(['order_date']);
            $table->dropIndex(['deleted_at']);
            $table->dropIndex(['payment_state', 'state']);
            $table->dropIndex(['system_delivery_date', 'system_delivery_date_end']);
        });

        Schema::table('transactions', function (Blueprint $table): void {
            $table->dropIndex(['booking_date']);
            $table->dropIndex(['deleted_at']);
            $table->dropIndex(['is_ignored', 'booking_date']);
        });

        Schema::table('work_times', function (Blueprint $table): void {
            $table->dropIndex(['deleted_at']);
            $table->dropIndex(['user_id', 'started_at']);
            $table->dropIndex(['employee_id', 'started_at']);
            $table->dropIndex(['is_locked', 'is_daily_work_time']);
        });

        Schema::table('schedules', function (Blueprint $table): void {
            $table->dropIndex(['is_active', 'due_at', 'ends_at']);
        });

        Schema::table('payment_reminders', function (Blueprint $table): void {
            $table->dropIndex(['deleted_at']);
            $table->dropIndex(['order_id', 'reminder_level']);
        });

        Schema::table('tickets', function (Blueprint $table): void {
            $table->dropIndex(['resolved_at']);
            $table->dropIndex(['deleted_at']);
            $table->dropIndex(['state', 'created_at']);
        });

        Schema::table('communications', function (Blueprint $table): void {
            $table->dropIndex(['deleted_at']);
            $table->dropIndex(['mail_folder_id', 'date']);
            $table->dropIndex(['mail_account_id', 'communication_type_enum']);
        });

        Schema::table('addresses', function (Blueprint $table): void {
            $table->dropIndex(['email_primary']);
            $table->dropIndex(['deleted_at']);
        });

        Schema::table('contacts', function (Blueprint $table): void {
            $table->dropIndex(['deleted_at']);
        });

        Schema::table('leads', function (Blueprint $table): void {
            $table->dropIndex(['deleted_at']);
        });

        Schema::table('order_positions', function (Blueprint $table): void {
            $table->dropIndex(['deleted_at']);
            $table->dropIndex(['order_id', 'sort_number']);
        });

        Schema::table('comments', function (Blueprint $table): void {
            $table->dropIndex(['deleted_at']);
        });

        Schema::table('event_subscriptions', function (Blueprint $table): void {
            $table->dropIndex(['subscribable_type', 'subscribable_id', 'event']);
        });

        Schema::table('notifications', function (Blueprint $table): void {
            $table->dropIndex(['notifiable_type', 'notifiable_id', 'read_at']);
        });

        Schema::table('employees', function (Blueprint $table): void {
            $table->dropIndex(['is_active']);
            $table->dropIndex(['deleted_at']);
        });

        Schema::table('purchase_invoices', function (Blueprint $table): void {
            $table->dropIndex(['invoice_date']);
            $table->dropIndex(['deleted_at']);
        });

        Schema::table('bank_connections', function (Blueprint $table): void {
            $table->dropIndex(['is_active']);
            $table->dropIndex(['deleted_at']);
        });
    }
};
