<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('payment_types')) {
            return;
        }

        Schema::create('payment_types', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36);
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('payment_reminder_days_1')->nullable();
            $table->integer('payment_reminder_days_2')->nullable();
            $table->integer('payment_reminder_days_3')->nullable();
            $table->integer('payment_target')->nullable();
            $table->integer('payment_discount_target')->nullable();
            $table->decimal('payment_discount_percentage')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_direct_debit')->default(false);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_purchase')->default(false);
            $table->boolean('is_sales')->default(true);
            $table->boolean('requires_manual_transfer')->default(false);
            $table->timestamp('created_at')->nullable()->comment('A timestamp reflecting the time of record-creation.');
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable()->comment('A timestamp reflecting the time of the last change for this record.');
            $table->string('updated_by')->nullable();
            $table->softDeletes()->comment('A timestamp reflecting the time of record-deletion.');
            $table->string('deleted_by')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_types');
    }
};
