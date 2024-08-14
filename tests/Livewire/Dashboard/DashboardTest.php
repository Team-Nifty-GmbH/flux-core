<?php

namespace FluxErp\Tests\Livewire\Dashboard;

use FluxErp\Models\Permission;
use FluxErp\Models\User;
use FluxErp\Models\Widget;
use FluxErp\Tests\Livewire\BaseSetup;
use FluxErp\Traits\Widgetable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Component;
use Livewire\Livewire;

class DashboardTest extends BaseSetup
{
    use DatabaseTransactions;

    public array $components = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->components[] = new class extends Component
        {
            use Widgetable;

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

        $this->components[] = new class extends Component
        {
            use Widgetable;

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
            'widgetable_type' => app(User::class)->getMorphClass(),
            'widgetable_id' => $this->user->id,
            'component_name' => 'sample-component',
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

    public function test_dashboard_widget_rendering()
    {
        // Perform the Livewire test
        Livewire::withoutLazyLoading()
            ->test('dashboard.dashboard')
            ->assertOk()
            ->assertSee('Hello from sample component')
            ->assertDontSee('Hello from sample component 2')
            ->assertSeeLivewire('sample-component')
            ->assertDontSeeLivewire('sample-component-2');
    }

    public function test_dashboard_hide_widget_without_permission()
    {
        Livewire::withoutLazyLoading()
            ->test('dashboard.dashboard')
            ->assertSeeLivewire('sample-component');

        Permission::findOrCreate('widget.sample-component');

        Livewire::withoutLazyLoading()
            ->test('dashboard.dashboard')
            ->assertOk()
            ->assertDontSeeLivewire('sample-component');
    }

    public function test_dashboard_show_widget_with_permission()
    {
        $permission = Permission::findOrCreate('widget.sample-component', 'web');

        Livewire::withoutLazyLoading()
            ->test('dashboard.dashboard')
            ->assertOk()
            ->assertDontSeeLivewire('sample-component');

        $this->user->givePermissionTo($permission);

        Livewire::withoutLazyLoading()
            ->test('dashboard.dashboard')
            ->assertOk()
            ->assertSeeLivewire('sample-component');
    }

    public function test_dashboard_widget_removal()
    {
        $livewire = Livewire::withoutLazyLoading()
            ->test('dashboard.dashboard');

        $livewire->assertSee('Hello from sample component')
            ->assertSeeLivewire('sample-component');

        $widgets = $livewire->get('widgets');
        unset($widgets[0]);

        $livewire->set('widgets', $widgets)
            ->call('saveDashboard')
            ->assertOk()
            ->call('$refresh')
            ->assertOk()
            ->assertDontSee('Hello from sample component')
            ->assertDontSeeLivewire('sample-component');
    }

    public function test_dashboard_update_widget()
    {
        Livewire::withoutLazyLoading()
            ->test('dashboard.dashboard')
            ->set('widgets.0.name', 'New Name')
            ->set('widgets.0.width', 3)
            ->set('widgets.0.height', 3)
            ->call('saveDashboard')
            ->assertOk()
            ->assertSet('widgets.0.name', 'New Name')
            ->assertSet('widgets.0.width', 3)
            ->assertSet('widgets.0.height', 3);
    }

    public function test_dashboard_unregistered_widget()
    {
        Livewire::withoutLazyLoading()
            ->test('dashboard.dashboard')
            ->assertSee('Hello from sample component')
            ->assertSeeLivewire('sample-component');

        \FluxErp\Facades\Widget::unregister('sample-component');

        Livewire::withoutLazyLoading()
            ->test('dashboard.dashboard')
            ->assertDontSee('Hello from sample component')
            ->assertDontSeeLivewire('sample-component');
    }
}
