<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->dropForeign('projects_category_id_foreign');
            $table->dropColumn(['category_id', 'display_name']);

            $table->foreignId('contact_id')
                ->nullable()
                ->after('uuid')
                ->constrained('contacts')
                ->nullOnDelete();
            $table->foreignId('order_id')
                ->nullable()
                ->after('contact_id')
                ->constrained('orders')
                ->nullOnDelete();
            $table->foreignId('responsible_user_id')
                ->nullable()
                ->after('order_id')
                ->constrained('users')
                ->nullOnDelete();
            $table->string('project_number')
                ->after('parent_id');
            $table->decimal('progress', 11, 10, true)
                ->after('state')
                ->default(0);
            $table->unsignedBigInteger('time_budget')
                ->nullable()
                ->comment('Time budget in minutes.')
                ->after('progress');
            $table->decimal('budget', 40, 10, true)
                ->nullable()
                ->after('time_budget');

            $table->renameColumn('project_name', 'name');
            $table->renameColumn('release_date', 'start_date');
            $table->renameColumn('deadline', 'end_date');
        });

        Schema::table('projects', function (Blueprint $table): void {
            $table->string('name')->change();
            $table->date('start_date')
                ->nullable()
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->renameColumn('name', 'project_name');
            $table->renameColumn('start_date', 'release_date');
            $table->renameColumn('end_date', 'deadline');

            $table->dropForeign('projects_contact_id_foreign');
            $table->dropForeign('projects_order_id_foreign');
            $table->dropForeign('projects_responsible_user_id_foreign');
            $table->dropColumn([
                'contact_id',
                'order_id',
                'responsible_user_id',
                'project_number',
                'progress',
                'time_budget',
                'budget',
            ]);
            $table->foreignId('category_id')
                ->nullable()
                ->after('uuid')
                ->constrained('categories');
            $table->json('display_name')
                ->nullable()
                ->after('name');
        });

        DB::statement(
            'UPDATE projects SET project_name = CONCAT(\'{"'
            . config('app.locale')
            . '": "\', REPLACE(project_name, \'"\', \'\\\\"\'), \'"}\')'
        );

        Schema::table('projects', function (Blueprint $table): void {
            $table->json('project_name')->change();
        });
    }
};
