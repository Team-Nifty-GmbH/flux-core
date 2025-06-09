<?php

namespace FluxErp\Tests\Feature\Console;

use FluxErp\Facades\Action;
use FluxErp\Facades\Widget;
use FluxErp\Htmlables\TabButton;
use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Livewire\Product\Product;
use FluxErp\Models\Permission;
use FluxErp\Tests\Feature\BaseSetup;
use FluxErp\Traits\Livewire\WithTabs;
use FluxErp\Traits\Widgetable;
use Illuminate\Support\Facades\Event;
use Livewire\Component;
use Livewire\Livewire;
use Livewire\Mechanisms\ComponentRegistry;
use function Livewire\invade;

class InitPermissionsTest extends BaseSetup
{
    public function test_init_permissions(): void
    {
        $actionsWithPermission = 0;
        foreach (Action::all() as $action) {
            if ($action['class']::hasPermission()) {
                $actionsWithPermission++;
            }
        }

        /** @var ComponentRegistry $registry */
        $registry = app(ComponentRegistry::class);
        $componentTabs = [];
        foreach (invade($registry)->aliases as $component) {
            if (! in_array(WithTabs::class, class_uses_recursive($component))) {
                continue;
            }

            $componentInstance = new $component();
            foreach ($componentInstance->getTabs() as $tab) {
                $componentTabs[] = 'tab.' . $tab->component;
            }
        }

        // Add Custom Widget
        Livewire::component('custom-widget-that-never-exists', new class() extends Component
        {
            use Widgetable;

            public static function dashboardComponent(): string
            {
                return Dashboard::class;
            }

            public function render()
            {
                return <<<'blade'
                    <div id="custom-widget">Hello from custom widget</div>
                blade;
            }

            public static function getLabel(): string
            {
                return 'Custom Widget';
            }
        });

        Widget::register('custom-widget-that-never-exists', 'custom-widget-that-never-exists');

        // Add Custom Tab
        Event::listen('tabs.rendering: ' . Product::class, function (Component $component): void {
            $component->mergeTabsToRender([
                TabButton::make('custom-tab-that-never-exists')->text('Custom Tab'),
            ]);
        });

        // Add unused permission
        Permission::create(['name' => 'unused.permission']);
        $this->assertDatabaseHas('permissions', ['name' => 'unused.permission']);

        // Execute artisan command
        $this->artisan('init:permissions')
            ->assertExitCode(0)
            ->expectsOutput('Registering action permissions for guard web…')
            ->expectsOutput('Registering action permissions for guard sanctum…')
            ->expectsOutput('Registering route permissions…')
            ->expectsOutput('Registering widget permissions…')
            ->expectsOutput('Registering tab permissions…');

        $this->assertDatabaseCount('roles', count(config('auth.guards')));

        $this->assertGreaterThan(
            0,
            Permission::query()
                ->whereNot('name', 'like', 'widget.%')
                ->whereNot('name', 'like', 'action.%')
                ->whereNot('name', 'like', 'tab.%')
                ->count()
        );

        // Assert all action permissions created
        $this->assertEquals(
            $actionsWithPermission,
            Permission::query()
                ->where('guard_name', 'web')
                ->where('name', 'like', 'action.%')
                ->count()
        );
        $this->assertEquals(
            $actionsWithPermission,
            Permission::query()
                ->where('guard_name', 'sanctum')
                ->where('name', 'like', 'action.%')
                ->count()
        );

        // Assert all widget permissions created
        $this->assertEquals(
            count(Widget::all()),
            Permission::query()->where('name', 'like', 'widget.%')->count()
        );

        // Assert all tab permissions created (plus one for custom tab)
        $this->assertEquals(
            count(array_unique($componentTabs)) + 1,
            Permission::query()->where('name', 'like', 'tab.%')->count()
        );

        // Assert custom widget permission created
        $this->assertDatabaseHas('permissions', ['name' => 'widget.custom-widget-that-never-exists']);

        // Assert custom tab permission created
        $this->assertDatabaseHas('permissions', ['name' => 'tab.custom-tab-that-never-exists']);

        // Assert unused permission removed
        $this->assertDatabaseMissing('permissions', ['name' => 'unused.permission']);
    }
}
