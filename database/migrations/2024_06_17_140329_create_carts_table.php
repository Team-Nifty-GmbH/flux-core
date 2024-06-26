<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->uuid();
            $table->nullableMorphs('authenticatable');
            $table->foreignId('payment_type_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('session_id')->index();
            $table->string('name')->nullable();
            $table->decimal('total', 40, 10)->nullable();
            $table->boolean('is_portal_public')->default(false);
            $table->boolean('is_public')->default(false);
            $table->boolean('is_watchlist')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
