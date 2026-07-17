<?php

use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Livewire\DataTables\GenerateWidgetWizard;
use FluxErp\Livewire\DataTables\OrderList;
use FluxErp\Models\Permission;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(GenerateWidgetWizard::class, ['datatable' => OrderList::class])
        ->assertOk();
});

test('redirects to dashboard for a class without widget generation', function (): void {
    Livewire::test(GenerateWidgetWizard::class, ['datatable' => FluxErp\Models\Order::class])
        ->assertRedirect(route('dashboard'));
});

test('next step validates the widget type', function (): void {
    Livewire::test(GenerateWidgetWizard::class, ['datatable' => OrderList::class])
        ->set('step', 2)
        ->call('nextStep')
        ->assertHasErrors(['widgetType'])
        ->set('widgetType', 'value_box')
        ->call('nextStep')
        ->assertHasNoErrors()
        ->assertSet('step', 3);
});

test('save creates a widget for the current user', function (): void {
    Livewire::test(GenerateWidgetWizard::class, ['datatable' => OrderList::class])
        ->set('widgetType', 'value_box')
        ->set('aggregate', 'count')
        ->set('name', 'Open Orders')
        ->set('targetDashboard', Dashboard::class)
        ->call('save')
        ->assertHasNoErrors();

    $widget = auth()->user()->widgets()->latest('id')->first();

    expect($widget)->not->toBeNull();
    expect($widget->name)->toBe('Open Orders');
    expect(data_get($widget->config, 'datatable'))->toBe(OrderList::class);
    expect(data_get($widget->config, 'aggregate'))->toBe('count');
});

test('is shared is stripped without the share permission', function (): void {
    Permission::findOrCreate('widget.generate-share', 'web');

    Livewire::test(GenerateWidgetWizard::class, ['datatable' => OrderList::class])
        ->set('widgetType', 'value_box')
        ->set('aggregate', 'count')
        ->set('name', 'Shared Widget')
        ->set('targetDashboard', Dashboard::class)
        ->set('isShared', true)
        ->call('save')
        ->assertHasNoErrors();

    $widget = auth()->user()->widgets()->latest('id')->first();

    expect(data_get($widget->config, 'is_shared'))->toBeFalse();
});
