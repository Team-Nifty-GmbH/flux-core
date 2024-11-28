<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('payment_reminders', function (Blueprint $table) {
            $table->foreign(['media_id'])->references(['id'])->on('media')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['order_id'])->references(['id'])->on('orders')->onUpdate('no action')->onDelete('no action');
        });
    }

    public function down(): void
    {
        Schema::table('payment_reminders', function (Blueprint $table) {
            $table->dropForeign('payment_reminders_media_id_foreign');
            $table->dropForeign('payment_reminders_order_id_foreign');
        });
    }
};
