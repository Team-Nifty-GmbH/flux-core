<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('work_times')) {
            return;
        }

        Schema::create('work_times', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36);
            $table->unsignedBigInteger('user_id')->index('work_times_user_id_foreign');
            $table->unsignedBigInteger('contact_id')->nullable()->index('work_times_contact_id_foreign');
            $table->unsignedBigInteger('order_position_id')->nullable()->unique();
            $table->unsignedBigInteger('parent_id')->nullable()->index('work_times_parent_id_foreign');
            $table->unsignedBigInteger('work_time_type_id')->nullable()->index('work_times_work_time_type_id_foreign');
            $table->string('trackable_type')->nullable();
            $table->unsignedBigInteger('trackable_id')->nullable();
            $table->dateTime('started_at');
            $table->dateTime('ended_at')->nullable();
            $table->unsignedBigInteger('paused_time_ms')->default(0);
            $table->bigInteger('total_time_ms')->default(0);
            $table->decimal('total_cost', 10)->nullable();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_billable')->default(false);
            $table->boolean('is_daily_work_time')->default(false);
            $table->boolean('is_locked')->default(false);
            $table->boolean('is_pause')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->string('deleted_by')->nullable();

            $table->index(['trackable_type', 'trackable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_times');
    }
};
