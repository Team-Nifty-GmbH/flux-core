<?php

use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Models\Permission;
use FluxErp\Models\User;
use FluxErp\Models\Widget;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->components[] = new class() extends Component
    {
        use FluxErp\Traits\Livewire\Widget\Widgetable;

        public static function dashboardComponent(): string
        {
            return Dashboard::class;
        }

        public static function getLabel(): string
        {
            return 'Sample Component';
        }

        public function render(): string
        {
            return <<<'blade'
                    <div id="sample-component">Hello from sample component</div>
                blade;
        }
    };
    $this->components[] = new class() extends Component
    {
        use FluxErp\Traits\Livewire\Widget\Widgetable;

        public static function dashboardComponent(): string
        {
            return Dashboard::class;
        }

        public static function getLabel(): string
        {
            return 'Sample Component 2';
        }

        public function render(): string
        {
            return <<<'blade'
                    <div id="sample-component-2">Hello from sample component 2</div>
                blade;
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
    FluxErp\Facades\Widget::register('sample-component', 'sample-component');
    FluxErp\Facades\Widget::register('sample-component-2', 'sample-component-2');

    $this->actingAs($this->user, 'web');
});
test('can add widget', function (): void {
    Livewire::withoutLazyLoading()
        ->actingAs($this->user, 'web')
        ->test(Dashboard::class)
        ->call('saveWidgets', [
            [
                'id' => Str::uuid()->toString(),
                'height' => 2,
                'width' => 2,
                'order_column' => 0,
                'order_row' => 0,
                'component_name' => $componentName = Str::uuid()->toString(),
            ],
        ])
        ->assertHasNoErrors();

    $this->assertDatabaseHas('widgets', [
        'widgetable_type' => morph_alias(User::class),
        'widgetable_id' => $this->user->id,
        'component_name' => $componentName,
        'dashboard_component' => Dashboard::class,
    ]);
});

test('dashboard hide widget without permission', function (): void {
    Livewire::withoutLazyLoading()
        ->test(Dashboard::class)
        ->assertSeeLivewire('sample-component');

    Permission::findOrCreate('widget.sample-component');

    Livewire::withoutLazyLoading()
        ->test(Dashboard::class)
        ->assertOk()
        ->assertDontSeeLivewire('sample-component');
});

test('dashboard hides widgets from different dashboard component', function (): void {
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
});

test('dashboard show widget with permission', function (): void {
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
});

test('dashboard unregistered widget', function (): void {
    Livewire::withoutLazyLoading()
        ->test(Dashboard::class)
        ->assertSee('Hello from sample component')
        ->assertSeeLivewire('sample-component');

    FluxErp\Facades\Widget::unregister('sample-component');

    Livewire::withoutLazyLoading()
        ->test(Dashboard::class)
        ->assertDontSee('Hello from sample component')
        ->assertDontSeeLivewire('sample-component');
});

test('dashboard update widget', function (): void {
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
});

test('dashboard widget removal', function (): void {
    $livewire = Livewire::withoutLazyLoading()
        ->test(Dashboard::class);

    $livewire->assertSee('Hello from sample component')
        ->assertSeeLivewire('sample-component');

    $widgets = $livewire->get('widgets');
    unset($widgets[0]);

    $livewire->set('widgets', $widgets)
        ->call('saveWidgets', $widgets)
        ->assertOk()
        ->call('widgets')
        ->assertOk()
        ->assertDontSee('Hello from sample component')
        ->assertDontSeeLivewire('sample-component');
});

test('dashboard widget rendering', function (): void {
    // Perform the Livewire test
    Livewire::withoutLazyLoading()
        ->test(Dashboard::class)
        ->assertOk()
        ->assertSee('Hello from sample component')
        ->assertDontSee('Hello from sample component 2')
        ->assertSeeLivewire('sample-component')
        ->assertDontSeeLivewire('sample-component-2');
});

test('filtering many permissioned widgets does not scan the permissions per widget', function (): void {
    foreach (range(1, 25) as $index) {
        Livewire::component('probe-widget-' . $index, $this->components[0]);
        FluxErp\Facades\Widget::register('probe-widget-' . $index, 'probe-widget-' . $index);
        Permission::findOrCreate('widget.probe-widget-' . $index, 'web');
    }

    $scans = countRegistrarScans(function (): void {
        Livewire::withoutLazyLoading()
            ->test(Dashboard::class)
            ->assertOk();
    });

    expect($scans)->toBeLessThan(10);
});

function createRoleWidget(FluxErp\Models\Role $role, string $componentName, ?string $group = null, int $width = 1): Widget
{
    return Widget::query()->create([
        'widgetable_type' => morph_alias(FluxErp\Models\Role::class),
        'widgetable_id' => $role->getKey(),
        'component_name' => $componentName,
        'dashboard_component' => Dashboard::class,
        'group' => $group,
        'width' => $width,
        'height' => 1,
    ]);
}

test('user without own widgets in a group sees the widgets of his role', function (): void {
    $role = FluxErp\Models\Role::findOrCreate('Sales', 'web');
    $this->user->assignRole($role);
    createRoleWidget($role, 'sample-component-2', 'Sales');

    $component = Livewire::withoutLazyLoading()
        ->test(Dashboard::class)
        ->set('group', 'Sales')
        ->assertOk();

    $salesWidgets = collect($component->get('widgets'))->where('group', 'Sales');

    expect($salesWidgets)->toHaveCount(1)
        ->and($salesWidgets->first()['component_name'])->toBe('sample-component-2')
        ->and(is_numeric($salesWidgets->first()['id']))->toBeFalse();
});

test('saving a group bound to a role creates an own copy and leaves the role untouched', function (): void {
    $role = FluxErp\Models\Role::findOrCreate('Sales', 'web');
    $this->user->assignRole($role);
    $roleWidget = createRoleWidget($role, 'sample-component-2', 'Sales');

    $component = Livewire::withoutLazyLoading()
        ->test(Dashboard::class)
        ->set('group', 'Sales');

    $widgets = collect($component->get('widgets'))->where('group', 'Sales')->values()->all();
    $widgets[0]['width'] = 4;

    $component->call('saveWidgets', $widgets)->assertOk();

    $this->assertDatabaseHas('widgets', [
        'widgetable_type' => morph_alias(User::class),
        'widgetable_id' => $this->user->getKey(),
        'component_name' => 'sample-component-2',
        'group' => 'Sales',
        'width' => 4,
    ]);
    expect($roleWidget->refresh()->width)->toBe(1);

    $roleWidget->update(['width' => 6]);

    $ownWidgets = collect(
        Livewire::withoutLazyLoading()
            ->test(Dashboard::class)
            ->get('widgets')
    )->where('group', 'Sales');

    expect($ownWidgets)->toHaveCount(1)
        ->and($ownWidgets->first()['width'])->toBe(4);
});

test('groups of several roles are merged and the role with the lowest id wins a shared group', function (): void {
    $first = FluxErp\Models\Role::findOrCreate('Sales', 'web');
    $second = FluxErp\Models\Role::findOrCreate('Accounting', 'web');
    $this->user->assignRole([$first, $second]);

    createRoleWidget($first, 'sample-component', 'Shared', 2);
    createRoleWidget($second, 'sample-component-2', 'Shared', 3);
    createRoleWidget($second, 'sample-component-2', 'Finance');

    $widgets = collect(Livewire::withoutLazyLoading()->test(Dashboard::class)->get('widgets'));

    expect($widgets->where('group', 'Shared'))->toHaveCount(1)
        ->and($widgets->where('group', 'Shared')->first()['width'])->toBe(2)
        ->and($widgets->where('group', 'Finance'))->toHaveCount(1);
});

test('saving the dashboard as role default replaces the widgets of the role', function (): void {
    $role = FluxErp\Models\Role::findOrCreate('Sales', 'web');
    $oldWidget = createRoleWidget($role, 'sample-component-2', 'Old');
    Permission::findOrCreate(route_to_permission('settings.permissions', false), 'web');
    $this->user->givePermissionTo(route_to_permission('settings.permissions', false));

    Livewire::withoutLazyLoading()
        ->test(Dashboard::class)
        ->assertSee('Save as role default')
        ->call('saveAsRoleDashboard', $role->getKey())
        ->assertOk();

    $this->assertModelMissing($oldWidget);
    $this->assertDatabaseHas('widgets', [
        'widgetable_type' => morph_alias(FluxErp\Models\Role::class),
        'widgetable_id' => $role->getKey(),
        'component_name' => 'sample-component',
        'dashboard_component' => Dashboard::class,
        'width' => 2,
    ]);
});

test('saving the dashboard as role default needs the permission to manage roles', function (): void {
    $role = FluxErp\Models\Role::findOrCreate('Sales', 'web');
    Permission::findOrCreate(route_to_permission('settings.permissions', false), 'web');

    Livewire::withoutLazyLoading()
        ->test(Dashboard::class)
        ->assertDontSee('Save as role default')
        ->call('saveAsRoleDashboard', $role->getKey())
        ->assertForbidden();

    $this->assertDatabaseMissing('widgets', [
        'widgetable_type' => morph_alias(FluxErp\Models\Role::class),
    ]);
});

test('resetting a group brings back the widgets of the role', function (): void {
    $role = FluxErp\Models\Role::findOrCreate('Sales', 'web');
    $this->user->assignRole($role);
    createRoleWidget($role, 'sample-component-2');

    $component = Livewire::withoutLazyLoading()
        ->test(Dashboard::class)
        ->assertSeeLivewire('sample-component')
        ->call('resetToRoleDashboard')
        ->assertOk();

    expect(collect($component->get('widgets'))->pluck('component_name')->all())->toBe(['sample-component-2']);
    $this->assertDatabaseMissing('widgets', [
        'widgetable_type' => morph_alias(User::class),
        'widgetable_id' => $this->user->getKey(),
        'component_name' => 'sample-component',
    ]);
});

test('applying a preset fills its group in the own dashboard', function (): void {
    Dashboard::registerPreset('finance', [
        ['component_name' => 'sample-component-2', 'group' => 'Finance', 'width' => 3, 'height' => 1],
    ]);

    $component = Livewire::withoutLazyLoading()
        ->test(Dashboard::class)
        ->assertSet('presets', ['finance'])
        ->call('applyPreset', 'finance')
        ->assertOk()
        ->assertSet('group', 'Finance');

    $this->assertDatabaseHas('widgets', [
        'widgetable_type' => morph_alias(User::class),
        'widgetable_id' => $this->user->getKey(),
        'component_name' => 'sample-component-2',
        'group' => 'Finance',
        'width' => 3,
    ]);
    expect(collect($component->get('widgets'))->where('group', 'Finance'))->toHaveCount(1);
});

test('a group that only exists for the role stays selected from the url', function (): void {
    $role = FluxErp\Models\Role::findOrCreate('Sales', 'web');
    $this->user->assignRole($role);
    createRoleWidget($role, 'sample-component-2', 'Sales');

    Livewire::withoutLazyLoading()
        ->withQueryParams(['group' => 'Sales'])
        ->test(Dashboard::class)
        ->assertSet('group', 'Sales');
});

test('deleting a role removes its dashboard widgets', function (): void {
    $role = FluxErp\Models\Role::findOrCreate('Sales', 'web');
    $widget = createRoleWidget($role, 'sample-component-2');

    FluxErp\Actions\Role\DeleteRole::make(['id' => $role->getKey()])
        ->validate()
        ->execute();

    $this->assertModelMissing($widget);
});
