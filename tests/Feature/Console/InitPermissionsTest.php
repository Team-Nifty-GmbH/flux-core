<?php

uses(FluxErp\Tests\Feature\BaseSetup::class);
use FluxErp\Facades\Action;
use FluxErp\Facades\Widget;
use FluxErp\Htmlables\TabButton;
use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Livewire\Product\Product;
use FluxErp\Models\Permission;
use FluxErp\Traits\Livewire\WithTabs;
use Illuminate\Support\Facades\Event;
use Livewire\Component;
use Livewire\Livewire;
use Livewire\Mechanisms\ComponentRegistry;
use function Livewire\invade;

test('init permissions', function (): void {
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
        use FluxErp\Traits\Widgetable;

        public static function dashboardComponent(): string
        {
            return Dashboard::class;
        }

        public static function getLabel(): string
        {
            return 'Custom Widget';
        }

        public function render()
        {
            return <<<'blade'
                    <div id="custom-widget">Hello from custom widget</div>
                blade;
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

    expect(Permission::query()
        ->whereNot('name', 'like', 'widget.%')
        ->whereNot('name', 'like', 'action.%')
        ->whereNot('name', 'like', 'tab.%')
        ->count())->toBeGreaterThan(0);

    // Assert all action permissions created
    expect(Permission::query()
        ->where('guard_name', 'web')
        ->where('name', 'like', 'action.%')
        ->count())->toEqual($actionsWithPermission);
    expect(Permission::query()
        ->where('guard_name', 'sanctum')
        ->where('name', 'like', 'action.%')
        ->count())->toEqual($actionsWithPermission);

    // Assert all widget permissions created
    expect(Permission::query()->where('name', 'like', 'widget.%')->count())->toEqual(count(Widget::all()));

    // Assert all tab permissions created (plus one for custom tab)
    expect(Permission::query()->where('name', 'like', 'tab.%')->count())->toEqual(count(array_unique($componentTabs)) + 1);

    // Assert custom widget permission created
    $this->assertDatabaseHas('permissions', ['name' => 'widget.custom-widget-that-never-exists']);

    // Assert custom tab permission created
    $this->assertDatabaseHas('permissions', ['name' => 'tab.custom-tab-that-never-exists']);

    // Assert unused permission removed
    $this->assertDatabaseMissing('permissions', ['name' => 'unused.permission']);
});
