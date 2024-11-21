<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tasks')) {
            return;
        }

        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36);
            $table->unsignedBigInteger('project_id')->nullable()->index('tasks_project_id_foreign');
            $table->unsignedBigInteger('responsible_user_id')->nullable()->index('tasks_responsible_user_id_foreign');
            $table->unsignedBigInteger('order_position_id')->nullable()->index('tasks_order_position_id_foreign');
            $table->string('model_type')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->unsignedInteger('priority')->default(0);
            $table->string('state')->default('open');
            $table->decimal('progress', 11, 10)->default(0);
            $table->unsignedBigInteger('time_budget')->nullable()->comment('Time budget in minutes.');
            $table->decimal('budget', 40, 10)->nullable();
            $table->decimal('total_cost', 10)->nullable();
            $table->timestamp('created_at')->nullable()->comment('A timestamp reflecting the time of record-creation.');
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable()->comment('A timestamp reflecting the time of the last change for this record.');
            $table->string('updated_by')->nullable();
            $table->softDeletes()->comment('A timestamp reflecting the time of record-deletion.');
            $table->string('deleted_by')->nullable();

            $table->index(['model_type', 'model_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
