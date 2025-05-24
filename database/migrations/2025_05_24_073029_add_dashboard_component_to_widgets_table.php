<?php

use FluxErp\Livewire\Dashboard\Dashboard;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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

        DB::table('widgets')
            ->update([
                'dashboard_component' => resolve_static(Dashboard::class, 'class'),
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
