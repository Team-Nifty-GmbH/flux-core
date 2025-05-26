<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('widgets', function (Blueprint $table): void {
            $table->string('dashboard_component')
                ->nullable()
                ->after('component_name');
            $table->string('group')
                ->nullable()
                ->after('dashboard_component');
        });

        try {
            $defaultDashboard = resolve_static(
                Route::getRoutes()
                    ->getByName('dashboard')
                    ->getAction('controller'),
                'class'
            );
        } catch (Throwable $e) {
            $defaultDashboard = 'unknown';
        }

        DB::table('widgets')
            ->update([
                'dashboard_component' => $defaultDashboard,
            ]);

        Schema::table('widgets', function (Blueprint $table): void {
            $table->string('dashboard_component')
                ->nullable(false)
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('widgets', function (Blueprint $table): void {
            $table->dropColumn(['dashboard_component', 'group']);
        });
    }
};
