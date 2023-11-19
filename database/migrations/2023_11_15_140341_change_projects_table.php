<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('name')->after('parent_id');
            $table->renameColumn('release_date', 'start_date');
            $table->renameColumn('deadline', 'end_date');
        });

        Schema::table('projects', function (Blueprint $table) {
            DB::statement('UPDATE projects SET name = project_name');

            $table->dropForeign('projects_category_id_foreign');
            $table->dropColumn(['category_id', 'display_name', 'project_name']);

            $table->foreignId('contact_id')
                ->after('uuid')
                ->nullable()
                ->constrained('contacts')
                ->onDelete('SET NULL');
            $table->foreignId('order_id')
                ->after('contact_id')
                ->nullable()
                ->constrained('orders')
                ->onDelete('SET NULL');
            $table->string('project_number')
                ->after('parent_id');
            $table->date('start_date')
                ->nullable()
                ->change();
            $table->integer('progress')
                ->after('state')
                ->default(0);
            $table->decimal('time_budget_hours', 7)
                ->nullable()
                ->after('progress');
            $table->decimal('budget', 40, 10)
                ->nullable()
                ->after('time_budget_hours');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->json('project_name')->nullable();
            DB::statement('UPDATE projects SET project_name = name');
            $table->json('project_name')->nullable(false)->change();
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign('projects_contact_id_foreign');
            $table->dropForeign('projects_order_id_foreign');
            $table->dropColumn([
                'contact_id',
                'order_id',
                'project_number',
                'name',
                'progress',
                'time_budget_hours',
                'budget',
            ]);
            $table->foreignId('category_id')
                ->after('uuid')
                ->nullable()
                ->constrained('categories');
            $table->string('display_name')
                ->after('name')
                ->nullable();
            $table->date('release_date')->after('display_name');
            $table->date('deadline')->after('release_date')->nullable();
            $table->json('project_name');
        });
    }
};
