<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36);
            $table->foreignId('ticket_type_id')
                ->nullable()
                ->constrained('ticket_types')
                ->nullOnDelete();
            $table->morphs('authenticatable');
            $table->nullableMorphs('model');
            $table->string('ticket_number');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('state')->nullable();
            $table->decimal('total_cost', 10)->nullable();
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
        Schema::dropIfExists('tickets');
    }
};
