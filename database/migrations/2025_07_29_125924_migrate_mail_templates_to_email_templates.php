<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class() extends Migration
{
    public function up(): void
    {
        $orderTypes = DB::table('order_types')
            ->select('id', 'name', 'mail_subject', 'mail_body')
            ->whereNotNull('mail_subject')
            ->orWhereNotNull('mail_body')
            ->get();

        foreach ($orderTypes as $orderType) {
            if ($orderType->mail_subject || $orderType->mail_body) {
                DB::table('email_templates')->insert([
                    'uuid' => Str::uuid()->toString(),
                    'name' => 'Order Type: ' . $orderType->name,
                    'model_type' => 'order',
                    'subject' => $orderType->mail_subject,
                    'html_body' => $orderType->mail_body,
                    'text_body' => strip_tags($orderType->mail_body ?? ''),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $paymentReminderTexts = DB::table('payment_reminder_texts')
            ->select('id', 'reminder_level', 'mail_subject', 'mail_body', 'mail_to', 'mail_cc')
            ->whereNotNull('mail_subject')
            ->orWhereNotNull('mail_body')
            ->get();

        foreach ($paymentReminderTexts as $reminderText) {
            if ($reminderText->mail_subject || $reminderText->mail_body) {
                DB::table('email_templates')->insert([
                    'uuid' => Str::uuid()->toString(),
                    'name' => 'Payment Reminder Level ' . $reminderText->reminder_level,
                    'model_type' => 'payment_reminder',
                    'subject' => $reminderText->mail_subject,
                    'html_body' => $reminderText->mail_body,
                    'text_body' => strip_tags($reminderText->mail_body ?? ''),
                    'to' => $reminderText->mail_to,
                    'cc' => $reminderText->mail_cc,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        Schema::table('order_types', function (Blueprint $table): void {
            $table->dropColumn(['mail_subject', 'mail_body']);
        });

        Schema::table('payment_reminder_texts', function (Blueprint $table): void {
            $table->dropColumn(['mail_subject', 'mail_body', 'mail_to', 'mail_cc']);
        });

        Schema::table('order_types', function (Blueprint $table): void {
            $table->foreignId('email_template_id')
                ->nullable()
                ->after('description')
                ->constrained('email_templates')
                ->nullOnDelete();
        });

        $existingOrderTypes = DB::table('order_types')->get(['id', 'name']);

        foreach ($existingOrderTypes as $orderType) {
            $template = DB::table('email_templates')
                ->where('name', 'Order Type: ' . $orderType->name)
                ->where('model_type', 'order')
                ->first();

            if ($template) {
                DB::table('order_types')
                    ->where('id', $orderType->id)
                    ->update(['email_template_id' => $template->id]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('order_types', function (Blueprint $table): void {
            $table->dropForeign(['email_template_id']);
            $table->dropColumn('email_template_id');
        });

        Schema::table('order_types', function (Blueprint $table): void {
            $table->string('mail_subject')->nullable();
            $table->text('mail_body')->nullable();
        });

        Schema::table('payment_reminder_texts', function (Blueprint $table): void {
            $table->string('mail_subject')->nullable();
            $table->text('mail_body')->nullable();
            $table->json('mail_to')->nullable();
            $table->json('mail_cc')->nullable();
        });

        DB::table('email_templates')
            ->where('name', 'like', 'Order Type:%')
            ->orWhere('name', 'like', 'Payment Reminder Level%')
            ->delete();
    }
};
