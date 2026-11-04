<?php

use FluxErp\Facades\Widget;
use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Models\Permission;
use Livewire\Component;
use Livewire\Livewire;

beforeEach(function (): void {
    Livewire::component('permission-widget', new class() extends Component
    {
        use FluxErp\Traits\Livewire\Widget\Widgetable;

        public static function dashboardComponent(): string
        {
            return Dashboard::class;
        }

        public function render(): string
        {
            return <<<'blade'
                    <div>Hello from permission widget</div>
                blade;
        }
    });
    Widget::register('permission-widget', 'permission-widget');

    $this->permission = Permission::findOrCreate('widget.permission-widget', 'web');
    $this->user->givePermissionTo($this->permission);
    $this->actingAs($this->user, 'web');
});

test('a snapshot from before the revocation no longer renders the widget', function (): void {
    $component = Livewire::test('permission-widget', ['dashboardComponent' => 'dashboard'])
        ->assertSee('Hello from permission widget');

    $this->user->revokePermissionTo($this->permission);

    $component->call('$refresh')
        ->assertForbidden();
});

test('a snapshot keeps rendering while the permission is granted', function (): void {
    Livewire::test('permission-widget', ['dashboardComponent' => 'dashboard'])
        ->call('$refresh')
        ->assertSee('Hello from permission widget');
});

test('a widget embedded outside a dashboard does not need the widget permission', function (): void {
    $component = Livewire::test('permission-widget');

    $this->user->revokePermissionTo($this->permission);

    $component->call('$refresh')
        ->assertSee('Hello from permission widget');
});
