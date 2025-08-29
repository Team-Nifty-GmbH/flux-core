<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('absence_policy_absence_type', function (Blueprint $table): void {
            $table->id('pivot_id');

            $table->foreignId('absence_policy_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('absence_type_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unique(['absence_policy_id', 'absence_type_id'], 'absence_policy_type_unique');
            $table->index('absence_type_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absence_policy_absence_type');
    }
};
