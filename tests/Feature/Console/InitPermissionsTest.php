<?php

namespace Tests\Feature;

use FluxErp\Facades\Action;
use FluxErp\Facades\Widget;
use FluxErp\Htmlables\TabButton;
use FluxErp\Livewire\Product\Product;
use FluxErp\Models\Permission;
use FluxErp\Tests\Feature\BaseSetup;
use FluxErp\Tests\TestCase;
use FluxErp\Traits\Livewire\WithTabs;
use FluxErp\Traits\Widgetable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Livewire\Component;
use Livewire\Livewire;
use Livewire\Mechanisms\ComponentRegistry;
use PHPUnit\Framework\Attributes\Test;

use function Livewire\invade;

class InitPermissionsTest extends BaseSetup
{
    use DatabaseTransactions;

    public function test_it_creates_permissions_for_routes()
    {
        $this->callInitPermissions();

        $this->assertDatabaseCount('roles', count(config('auth.guards')));

        $this->assertGreaterThan(
            0,
            Permission::query()
                ->whereNot('name', 'like', 'widget.%')
                ->whereNot('name', 'like', 'action.%')
                ->whereNot('name', 'like', 'tab.%')
                ->count()
        );
    }

    public function test_it_creates_permissions_for_actions()
    {
        $this->callInitPermissions();

        $actionsWithPermission = 0;
        foreach (Action::all() as $action) {
            if ($action['class']::hasPermission()) {
                $actionsWithPermission++;
            }
        }

        $this->assertEquals($actionsWithPermission, Permission::query()->where('name', 'like', 'action.%')->count());
    }

    public function test_it_creates_permissions_for_widgets()
    {
        $this->callInitPermissions();

        $this->assertEquals(count(Widget::all()), Permission::query()->where('name', 'like', 'widget.%')->count());
    }

    public function test_it_creates_permission_for_custom_widget()
    {
        Livewire::component('custom-widget-that-never-exists', new class extends Component
        {
            use Widgetable;

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

        $this->callInitPermissions();

        $this->assertDatabaseHas('permissions', ['name' => 'widget.custom-widget-that-never-exists']);
    }

    public function test_it_creates_permissions_for_tabs()
    {
        $this->callInitPermissions();

        /** @var ComponentRegistry $registry */
        $registry = app(ComponentRegistry::class);
        $componentTabs = [];
        foreach (invade($registry)->aliases as $component) {
            if (! in_array(WithTabs::class, class_uses_recursive($component))) {
                continue;
            }

            $componentInstance = new $component;
            foreach ($componentInstance->getTabs() as $tab) {
                $componentTabs[] = 'tab.' . $tab->component;
            }
        }

        $this->assertEquals(
            count(array_unique($componentTabs)),
            Permission::query()->where('name', 'like', 'tab.%')->count()
        );
    }

    public function test_it_creates_permission_for_custom_tab()
    {
        Event::listen('tabs.rendering: ' . Product::class, function (Component $component) {
            $component->mergeTabsToRender([
                TabButton::make('custom-tab-that-never-exists', label: 'Custom Tab'),
            ]);
        });

        $this->callInitPermissions();

        $this->assertDatabaseHas('permissions', ['name' => 'tab.custom-tab-that-never-exists']);
    }

    public function test_it_deletes_unused_permissions()
    {
        Permission::create(['name' => 'unused.permission']);
        $this->assertDatabaseHas('permissions', ['name' => 'unused.permission']);

        $this->artisan('init:permissions')
            ->assertExitCode(0);

        $this->assertDatabaseMissing('permissions', ['name' => 'unused.permission']);
    }

    private function callInitPermissions(): void
    {
        $this->artisan('init:permissions')
            ->assertExitCode(0)
            ->expectsOutput('Registering action permissions…')
            ->expectsOutput('Registering route permissions…')
            ->expectsOutput('Registering widget permissions…')
            ->expectsOutput('Registering tab permissions…');
    }
}
