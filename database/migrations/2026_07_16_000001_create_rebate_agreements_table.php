<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('rebate_agreements', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);
            $table->foreignId('contact_id')
                ->constrained('contacts')
                ->cascadeOnDelete();
            $table->foreignId('rebate_order_id')
                ->nullable()
                ->constrained('orders')
                ->nullOnDelete();
            $table->string('name')->nullable();
            $table->date('period_start');
            $table->date('period_end');
            $table->json('tiers')
                ->comment('The volume tiers, each containing a from_volume and the percentage granted from it.');
            $table->timestamp('settled_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('deleted_by')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rebate_agreements');
    }
};
