<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('carts')) {
            return;
        }

        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36);
            $table->string('authenticatable_type')->nullable();
            $table->unsignedBigInteger('authenticatable_id')->nullable();
            $table->unsignedBigInteger('payment_type_id')->nullable()->index('carts_payment_type_id_foreign');
            $table->unsignedBigInteger('price_list_id')->index('carts_price_list_id_foreign');
            $table->string('session_id')->index();
            $table->string('name')->nullable();
            $table->decimal('total', 40, 10)->nullable();
            $table->boolean('is_portal_public')->default(false);
            $table->boolean('is_public')->default(false);
            $table->boolean('is_watchlist')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->string('deleted_by')->nullable();

            $table->index(['authenticatable_type', 'authenticatable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
