<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('addresses', function (Blueprint $table): void {
            $table->index('deleted_at');
            $table->index('email_primary');
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
            $table->index('mail_account_id');
            $table->index('mail_folder_id');
        });

        Schema::table('contacts', function (Blueprint $table): void {
            $table->index('deleted_at');
        });

        Schema::table('employees', function (Blueprint $table): void {
            $table->index('deleted_at');
        });

        Schema::table('event_subscriptions', function (Blueprint $table): void {
            $table->index(
                ['subscribable_type', 'subscribable_id', 'event'],
                'event_subscriptions_subscribable_event_index',
            );
        });

        Schema::table('leads', function (Blueprint $table): void {
            $table->index('deleted_at');
        });

        Schema::table('notifications', function (Blueprint $table): void {
            $table->index('read_at');
            $table->index(['notifiable_type', 'notifiable_id', 'read_at']);
        });

        Schema::table('order_positions', function (Blueprint $table): void {
            $table->index('deleted_at');
            $table->index(['order_id', 'sort_number']);
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->index('deleted_at');
            $table->index('invoice_date');
            $table->index('order_date');
            $table->index('payment_reminder_next_date');
            $table->index('payment_state');
            $table->index('state');
            $table->index(['system_delivery_date', 'system_delivery_date_end']);
        });

        Schema::table('payment_reminders', function (Blueprint $table): void {
            $table->index('deleted_at');
            $table->index(['order_id', 'reminder_level']);
        });

        Schema::table('purchase_invoices', function (Blueprint $table): void {
            $table->index('deleted_at');
            $table->index('invoice_date');
        });

        Schema::table('schedules', function (Blueprint $table): void {
            $table->index(['due_at', 'ends_at']);
        });

        Schema::table('tickets', function (Blueprint $table): void {
            $table->index('deleted_at');
            $table->index('resolved_at');
            $table->index(['state', 'created_at']);
        });

        Schema::table('transactions', function (Blueprint $table): void {
            $table->index('booking_date');
            $table->index('deleted_at');
        });

        Schema::table('work_times', function (Blueprint $table): void {
            $table->index('deleted_at');
            $table->index(['employee_id', 'started_at']);
            $table->index(['user_id', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::table('work_times', function (Blueprint $table): void {
            $table->dropIndex(['user_id', 'started_at']);
            $table->dropIndex(['employee_id', 'started_at']);
            $table->dropIndex(['deleted_at']);
        });

        Schema::table('transactions', function (Blueprint $table): void {
            $table->dropIndex(['deleted_at']);
            $table->dropIndex(['booking_date']);
        });

        Schema::table('tickets', function (Blueprint $table): void {
            $table->dropIndex(['state', 'created_at']);
            $table->dropIndex(['resolved_at']);
            $table->dropIndex(['deleted_at']);
        });

        Schema::table('schedules', function (Blueprint $table): void {
            $table->dropIndex(['due_at', 'ends_at']);
        });

        Schema::table('purchase_invoices', function (Blueprint $table): void {
            $table->dropIndex(['invoice_date']);
            $table->dropIndex(['deleted_at']);
        });

        Schema::table('payment_reminders', function (Blueprint $table): void {
            $table->dropIndex(['order_id', 'reminder_level']);
            $table->dropIndex(['deleted_at']);
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropIndex(['system_delivery_date', 'system_delivery_date_end']);
            $table->dropIndex(['state']);
            $table->dropIndex(['payment_state']);
            $table->dropIndex(['payment_reminder_next_date']);
            $table->dropIndex(['order_date']);
            $table->dropIndex(['invoice_date']);
            $table->dropIndex(['deleted_at']);
        });

        Schema::table('order_positions', function (Blueprint $table): void {
            $table->dropIndex(['order_id', 'sort_number']);
            $table->dropIndex(['deleted_at']);
        });

        Schema::table('notifications', function (Blueprint $table): void {
            $table->dropIndex(['notifiable_type', 'notifiable_id', 'read_at']);
            $table->dropIndex(['read_at']);
        });

        Schema::table('leads', function (Blueprint $table): void {
            $table->dropIndex(['deleted_at']);
        });

        Schema::table('event_subscriptions', function (Blueprint $table): void {
            $table->dropIndex('event_subscriptions_subscribable_event_index');
        });

        Schema::table('employees', function (Blueprint $table): void {
            $table->dropIndex(['deleted_at']);
        });

        Schema::table('contacts', function (Blueprint $table): void {
            $table->dropIndex(['deleted_at']);
        });

        Schema::table('communications', function (Blueprint $table): void {
            $table->dropIndex(['mail_folder_id']);
            $table->dropIndex(['mail_account_id']);
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
            $table->dropIndex(['email_primary']);
            $table->dropIndex(['deleted_at']);
        });
    }
};
