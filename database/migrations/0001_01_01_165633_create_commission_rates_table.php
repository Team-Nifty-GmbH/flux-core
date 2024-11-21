<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('commission_rates')) {
            return;
        }

        Schema::create('commission_rates', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36);
            $table->unsignedBigInteger('user_id')->index('commission_rates_user_id_foreign');
            $table->unsignedBigInteger('contact_id')->nullable()->index('commission_rates_contact_id_foreign');
            $table->unsignedBigInteger('category_id')->nullable()->index('commission_rates_category_id_foreign');
            $table->unsignedBigInteger('product_id')->nullable()->index('commission_rates_product_id_foreign');
            $table->decimal('commission_rate', 11, 10)->unsigned();
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
        Schema::dropIfExists('commission_rates');
    }
};
