<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('rule_conditions', function (Blueprint $table): void {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->foreignId('parent_id')->nullable()->constrained('rule_conditions')->cascadeOnDelete();
            $table->foreignId('rule_id')->constrained('rules')->cascadeOnDelete();
            $table->string('type');
            $table->json('value')->nullable();
            $table->integer('position')->default(0);
            $table->timestamp('created_at')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('updated_by')->nullable();

            $table->index(['rule_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rule_conditions');
    }
};
