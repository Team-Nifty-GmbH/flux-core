<?php

use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Models\User;
use FluxErp\Models\Widget;

beforeEach(function (): void {
    Widget::query()->create([
        'widgetable_type' => morph_alias(User::class),
        'widgetable_id' => $this->user->getKey(),
        'component_name' => 'widgets.average-order-value',
        'dashboard_component' => Dashboard::class,
        'name' => 'Average Order Value',
        'width' => 6,
        'height' => 4,
        'order_column' => 0,
        'order_row' => 0,
    ]);
});

/**
 * Nothing on the server says whether a chart reached the screen: the widget
 * renders its container either way, and the drawing happens in the browser.
 * The canvas below only exists once ApexCharts has been loaded, constructed
 * and rendered, which is what makes this the guard for any change to how that
 * library is bundled or loaded.
 */
test('a chart widget renders its canvas', function (): void {
    $page = visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke();

    waitForCondition($page, "() => !! document.querySelector('.apexcharts-canvas')", 15000)
        ->assertScript("document.querySelectorAll('.apexcharts-canvas').length >= 1")
        ->assertScript("typeof window.ApexCharts === 'function'");
});
