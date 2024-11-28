<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('payment_reminders')) {
            return;
        }

        Schema::create('payment_reminders', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36);
            $table->unsignedBigInteger('order_id')->index('payment_reminders_order_id_foreign');
            $table->unsignedBigInteger('media_id')->nullable()->index('payment_reminders_media_id_foreign');
            $table->unsignedInteger('reminder_level');
            $table->timestamp('created_at')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('updated_by')->nullable();
            $table->softDeletes();
            $table->string('deleted_by')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_reminders');
    }
};
