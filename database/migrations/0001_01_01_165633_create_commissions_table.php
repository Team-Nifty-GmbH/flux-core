<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('commissions')) {
            return;
        }

        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36);
            $table->unsignedBigInteger('user_id')->index('commissions_user_id_foreign');
            $table->unsignedBigInteger('commission_rate_id')->nullable()->index('commissions_commission_rate_id_foreign');
            $table->unsignedBigInteger('order_id')->nullable()->index('commissions_order_id_foreign');
            $table->unsignedBigInteger('order_position_id')->nullable()->index('commissions_order_position_id_foreign');
            $table->unsignedBigInteger('credit_note_order_position_id')->nullable()->index('commissions_credit_note_order_position_id_foreign');
            $table->json('commission_rate');
            $table->decimal('total_net_price', 40, 10);
            $table->decimal('commission', 40, 10);
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
        Schema::dropIfExists('commissions');
    }
};
