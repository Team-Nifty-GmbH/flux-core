<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('fixed_asset_depreciations', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);
            $table->foreignId('fixed_asset_id')
                ->constrained('fixed_assets')
                ->cascadeOnDelete();
            $table->unsignedInteger('sequence');
            $table->date('booking_date');
            $table->decimal('amount', 40, 10);
            $table->decimal('book_value_after', 40, 10);
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
        Schema::dropIfExists('fixed_asset_depreciations');
    }
};
