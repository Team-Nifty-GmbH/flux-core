<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->string('model_type')->nullable()->after('order_position_id');
            $table->unsignedBigInteger('model_id')->nullable()->after('model_type');

            $table->index(['model_type', 'model_id']);
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex('tasks_model_type_model_id_index');
            $table->dropColumn([
                'model_type',
                'model_id',
            ]);
        });
    }
};
