<?php

namespace FluxErp\Tests\Livewire\Dashboard;

use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Models\Permission;
use FluxErp\Models\User;
use FluxErp\Models\Widget;
use FluxErp\Tests\Livewire\BaseSetup;
use FluxErp\Traits\Widgetable;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Livewire;

class DashboardTest extends BaseSetup
{
    public array $components = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->components[] = new class() extends Component
        {
            use Widgetable;

            public static function dashboardComponent(): string
            {
                return Dashboard::class;
            }

            public function render(): string
            {
                return <<<'blade'
                    <div id="sample-component">Hello from sample component</div>
                blade;
            }

            public static function getLabel(): string
            {
                return 'Sample Component';
            }
        };

        $this->components[] = new class() extends Component
        {
            use Widgetable;

            public static function dashboardComponent(): string
            {
                return Dashboard::class;
            }

            public function render(): string
            {
                return <<<'blade'
                    <div id="sample-component-2">Hello from sample component 2</div>
                blade;
            }

            public static function getLabel(): string
            {
                return 'Sample Component 2';
            }
        };

        Widget::query()->create([
            'widgetable_type' => morph_alias(User::class),
            'widgetable_id' => $this->user->id,
            'component_name' => 'sample-component',
            'dashboard_component' => data_get($this->components, '0')::dashboardComponent(),
            'name' => 'Widget 1',
            'width' => 2,
            'height' => 1,
        ]);

        Livewire::component('sample-component', $this->components[0]);
        Livewire::component('sample-component-2', $this->components[1]);
        \FluxErp\Facades\Widget::register('sample-component', 'sample-component');
        \FluxErp\Facades\Widget::register('sample-component-2', 'sample-component-2');

        $this->actingAs($this->user, 'web');
    }

    public function test_can_add_widget(): void
    {
        Livewire::withoutLazyLoading()
            ->actingAs($this->user, 'web')
            ->test(Dashboard::class)
            ->call('saveWidgets', [
                [
                    'id' => Str::uuid(),
                    'height' => 2,
                    'width' => 2,
                    'order_column' => 0,
                    'order_row' => 0,
                    'component_name' => $componentName = Str::uuid(),
                ],
            ])
            ->assertHasNoErrors();

        $this->assertDatabaseHas('widgets', [
            'widgetable_type' => morph_alias(User::class),
            'widgetable_id' => $this->user->id,
            'component_name' => $componentName,
            'dashboard_component' => Dashboard::class,
        ]);
    }

    public function test_dashboard_hide_widget_without_permission(): void
    {
        Livewire::withoutLazyLoading()
            ->test(Dashboard::class)
            ->assertSeeLivewire('sample-component');

        Permission::findOrCreate('widget.sample-component');

        Livewire::withoutLazyLoading()
            ->test(Dashboard::class)
            ->assertOk()
            ->assertDontSeeLivewire('sample-component');
    }

    public function test_dashboard_hides_widgets_from_different_dashboard_component(): void
    {
        Widget::query()->create([
            'widgetable_type' => morph_alias(User::class),
            'widgetable_id' => $this->user->id,
            'component_name' => 'sample-component-other-dashboard',
            'dashboard_component' => 'App\Livewire\SomeOtherDashboard',
            'name' => 'Widget for Other Dashboard',
            'width' => 2,
            'height' => 1,
        ]);

        $component = Livewire::withoutLazyLoading()->test(Dashboard::class);

        $component->assertOk()
            ->assertSeeLivewire('sample-component')
            ->assertDontSeeLivewire('sample-component-2');

        $this->assertArrayNotHasKey('sample-component-from-other-dashboard', $component->get('widgets'));
    }

    public function test_dashboard_show_widget_with_permission(): void
    {
        $permission = Permission::findOrCreate('widget.sample-component', 'web');

        Livewire::withoutLazyLoading()
            ->test(Dashboard::class)
            ->assertOk()
            ->assertDontSeeLivewire('sample-component');

        $this->user->givePermissionTo($permission);

        Livewire::withoutLazyLoading()
            ->test(Dashboard::class)
            ->assertOk()
            ->assertSeeLivewire('sample-component');
    }

    public function test_dashboard_unregistered_widget(): void
    {
        Livewire::withoutLazyLoading()
            ->test(Dashboard::class)
            ->assertSee('Hello from sample component')
            ->assertSeeLivewire('sample-component');

        \FluxErp\Facades\Widget::unregister('sample-component');

        Livewire::withoutLazyLoading()
            ->test(Dashboard::class)
            ->assertDontSee('Hello from sample component')
            ->assertDontSeeLivewire('sample-component');
    }

    public function test_dashboard_update_widget(): void
    {
        $livewire = Livewire::withoutLazyLoading()
            ->test(Dashboard::class);

        $widgets = $livewire->get('widgets');
        $widgets[0]['name'] = 'New Name';
        $widgets[0]['width'] = 3;
        $widgets[0]['height'] = 3;

        $livewire->call('saveWidgets', $widgets)
            ->assertOk()
            ->assertSet('widgets.0.name', 'New Name')
            ->assertSet('widgets.0.width', 3)
            ->assertSet('widgets.0.height', 3);
    }

    public function test_dashboard_widget_removal(): void
    {
        $livewire = Livewire::withoutLazyLoading()
            ->test(Dashboard::class);

        $livewire->assertSee('Hello from sample component')
            ->assertSeeLivewire('sample-component');

        $widgets = $livewire->get('widgets');
        unset($widgets[0]);

        $livewire->set('widgets', $widgets)
            ->call('saveWidgets', $widgets)
            ->assertOk()
            ->call('$refresh')
            ->assertOk()
            ->assertDontSee('Hello from sample component')
            ->assertDontSeeLivewire('sample-component');
    }

    public function test_dashboard_widget_rendering(): void
    {
        // Perform the Livewire test
        Livewire::withoutLazyLoading()
            ->test(Dashboard::class)
            ->assertOk()
            ->assertSee('Hello from sample component')
            ->assertDontSee('Hello from sample component 2')
            ->assertSeeLivewire('sample-component')
            ->assertDontSeeLivewire('sample-component-2');
    }
}
