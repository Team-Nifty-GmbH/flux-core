<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('communicatable')) {
            Schema::create('communicatable', function (Blueprint $table): void {
                $table->id();
                $table->morphs('communicatable');
                $table->foreignId('communication_id')->constrained('communications')->cascadeOnDelete();

                $table->unique(
                    ['communicatable_type', 'communicatable_id', 'communication_id'],
                    'communicatable_type_id_communication_id_unique'
                );
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('communicatable');
    }
};
