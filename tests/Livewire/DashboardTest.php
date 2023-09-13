<?php

namespace FluxErp\Tests\Livewire;

use FluxErp\Contracts\UserWidget;
use FluxErp\Models\Permission;
use FluxErp\Models\User;
use FluxErp\Models\Widget;
use FluxErp\Tests\Feature\BaseSetup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Component;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;

class DashboardTest extends BaseSetup
{
    use DatabaseTransactions;

    public array $components = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->components[] = new class extends Component implements UserWidget
        {
            public function render(): string
            {
                return <<<'blade'
                    <div>Hello from sample component</div>
                blade;
            }

            public static function getLabel(): string
            {
                return 'Sample Component';
            }
        };

        $this->components[] = new class extends Component implements UserWidget
        {
            public function render(): string
            {
                return <<<'blade'
                    <div>Hello from sample component 2</div>
                blade;
            }

            public static function getLabel(): string
            {
                return 'Sample Component 2';
            }
        };

        Widget::query()->create([
            'widgetable_type' => User::class,
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
        Livewire::test('dashboard.dashboard')
            ->assertSee('Hello from sample component')
            ->assertDontSee('Hello from sample component 2')
            ->assertSeeLivewire('sample-component')
            ->assertDontSeeLivewire('sample-component-2');
    }

    public function test_dashboard_hide_widget_without_permission()
    {
        Livewire::test('dashboard.dashboard')
            ->assertSeeLivewire('sample-component');

        Permission::findOrCreate('widget.sample-component');
        $this->app->make(PermissionRegistrar::class)->registerPermissions();

        Livewire::test('dashboard.dashboard')
            ->assertOk()
            ->assertDontSeeLivewire('sample-component');
    }

    public function test_dashboard_show_widget_with_permission()
    {
        $permission = Permission::findOrCreate('widget.sample-component');
        $this->app->make(PermissionRegistrar::class)->registerPermissions();

        Livewire::test('dashboard.dashboard')
            ->assertOk()
            ->assertDontSeeLivewire('sample-component');

        $this->user->givePermissionTo($permission);

        Livewire::test('dashboard.dashboard')
            ->assertOk()
            ->assertSeeLivewire('sample-component');
    }

    public function test_dashboard_widget_adding()
    {
        // Add another widget to the dashboard
        Livewire::test('dashboard.dashboard')
            ->call('saveWidgets', [$this->user->widgets->first()->id, 'new-' . 'sample-component-2'])
            ->assertOk()
            ->assertSee('Hello from sample component')
            ->assertSee('Hello from sample component 2')
            ->assertSeeLivewire('sample-component')
            ->assertSeeLivewire('sample-component-2');
    }

    public function test_dashboard_widget_removal()
    {
        Livewire::test('dashboard.dashboard')
            ->call('saveWidgets', [$this->user->widgets->first()->id])
            ->assertOk()
            ->assertSee('Hello from sample component')
            ->assertDontSee('Hello from sample component 2')
            ->assertSeeLivewire('sample-component')
            ->assertDontSeeLivewire('sample-component-2');
    }

    public function test_dashboard_update_widget()
    {
        Livewire::test('dashboard.dashboard')
            ->call('updateWidget',
                [
                    'id' => $this->user->widgets->first()->id,
                    'name' => 'New Name',
                    'width' => 3,
                    'height' => 3,
                ]
            )
            ->assertOk()
            ->assertSet('widgets.0.name', 'New Name')
            ->assertSet('widgets.0.width', 3)
            ->assertSet('widgets.0.height', 3);
    }

    public function test_dashboard_unregistered_widget()
    {
        Livewire::test('dashboard.dashboard')
            ->assertSee('Hello from sample component')
            ->assertSeeLivewire('sample-component');

        \FluxErp\Facades\Widget::unregister('sample-component');

        Livewire::test('dashboard.dashboard')
            ->assertDontSee('Hello from sample component')
            ->assertDontSeeLivewire('sample-component');
    }
}
