<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('projects')) {
            return;
        }

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36);
            $table->unsignedBigInteger('client_id')->index('projects_client_id_foreign');
            $table->unsignedBigInteger('contact_id')->nullable()->index('projects_contact_id_foreign');
            $table->unsignedBigInteger('order_id')->nullable()->index('projects_order_id_foreign');
            $table->unsignedBigInteger('responsible_user_id')->nullable()->index('projects_responsible_user_id_foreign');
            $table->unsignedBigInteger('parent_id')->nullable()->index('projects_parent_id_foreign');
            $table->string('project_number');
            $table->string('name');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('description')->nullable();
            $table->string('state')->default('open');
            $table->decimal('progress', 11, 10)->default(0);
            $table->unsignedBigInteger('time_budget')->nullable()->comment('Time budget in minutes.');
            $table->decimal('budget', 40, 10)->nullable();
            $table->decimal('total_cost', 40, 10)->nullable();
            $table->timestamp('created_at')->nullable()->comment('A timestamp reflecting the time of record-creation.');
            $table->string('created_by')->nullable();
            $table->timestamp('updated_at')->nullable()->comment('A timestamp reflecting the time of the last change for this record.');
            $table->string('updated_by')->nullable();
            $table->softDeletes()->comment('A timestamp reflecting the time of record-deletion.');
            $table->string('deleted_by')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
