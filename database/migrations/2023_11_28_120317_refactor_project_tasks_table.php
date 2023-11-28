<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('project_tasks', function (Blueprint $table) {
            $table->dropForeign('project_tasks_address_id_foreign');
            $table->dropForeign('project_tasks_order_position_id_foreign');
            $table->dropForeign('project_tasks_project_id_foreign');
            $table->dropForeign('project_tasks_user_id_foreign');
            $table->dropColumn([
                'address_id',
                'is_paid',
                'is_done',
            ]);

            $table->renameColumn('user_id', 'responsible_user_id');
            $table->unsignedBigInteger('project_id')->nullable()->change();
            $table->string('name')->change();
            $table->text('description')->nullable()->after('name');
            $table->unsignedInteger('priority')->default(0)->after('description');

            $table->date('start_date')->nullable()->after('description');
            $table->date('due_date')->nullable()->after('start_date');
            $table->dateTime('started_at')->nullable()->after('due_date');
            $table->dateTime('ended_at')->nullable()->after('started_at');
            $table->decimal('progress', 11, 10, true)
                ->after('state')
                ->default(0);
            $table->decimal('time_budget_hours', 12, 2, true)
                ->nullable()
                ->after('progress');
            $table->decimal('budget', 40, 10, true)
                ->nullable()
                ->after('time_budget_hours');
        });

        Schema::rename('project_tasks', 'tasks');

        Schema::table('tasks', function (Blueprint $table) {
            $table->unsignedBigInteger('responsible_user_id')->nullable()->change();

            $table->foreign('project_id')->references('id')->on('projects')->nullOnDelete();
            $table->foreign('responsible_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('order_position_id')->references('id')->on('order_positions')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('tasks', 'project_tasks');

        DB::statement(
            'UPDATE project_tasks SET name = CONCAT(\'{"'
            . config('app.locale')
            . '": "\', REPLACE(name, \'"\', \'\\\\"\'), \'"}\')'
        );

        Schema::table('project_tasks', function (Blueprint $table) {
            $table->renameColumn('responsible_user_id', 'user_id');

            $table->dropColumn([
                'description',
                'start_date',
                'end_date',
                'started_at',
                'ended_at',
                'priority',
                'progress',
                'time_budget_hours',
                'budget',
            ]);

            $table->foreignId('address_id')->nullable()->references('id')->on('addresses')->nullOnDelete();
            $table->json('name')->change();
        });
    }
};
