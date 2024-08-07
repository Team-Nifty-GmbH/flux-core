<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToContactsTable extends Migration
{
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->boolean('is_delivery_lock')->default(false)->after('uuid');
            $table->boolean('is_sensitive_reminder')->default(false)->after('uuid');
            $table->decimal('credit_line')->nullable()->after('uuid');
            $table->double('discount_percent')->nullable()->after('uuid');
            $table->integer('discount_days')->nullable()->after('uuid');
            $table->integer('payment_reminder_days_3')->nullable()->after('uuid');
            $table->integer('payment_reminder_days_2')->nullable()->after('uuid');
            $table->integer('payment_reminder_days_1')->nullable()->after('uuid');
            $table->integer('payment_target_days')->nullable()->after('uuid');
            $table->string('creditor_number')->nullable()->after('uuid');
            $table->string('customer_number')->after('uuid')->index();
            $table->unsignedBigInteger('client_id')->after('uuid');
            $table->unsignedBigInteger('price_list_id')->nullable()->after('uuid');
            $table->unsignedBigInteger('payment_type_id')->nullable()->after('uuid');
        });
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn([
                'customer_number',
                'creditor_number',
                'payment_target_days',
                'payment_reminder_days_1',
                'payment_reminder_days_2',
                'payment_reminder_days_3',
                'discount_days',
                'discount_percent',
                'credit_line',
                'is_sensitive_reminder',
                'is_delivery_lock',
                'client_id',
                'price_list_id',
                'payment_type_id',
            ]);
        });
    }
}
