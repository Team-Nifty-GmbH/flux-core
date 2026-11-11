<?php

use FluxErp\Livewire\Dashboard\Dashboard;
use FluxErp\Models\Widget;

test('dashboard loads without js errors', function (): void {
    visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke();
});

test('dashboard renders grid container', function (): void {
    visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke()
        ->assertScript("!!document.querySelector('.grid-stack')");
});

test('dashboard widget add button exists in edit mode', function (): void {
    visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke()
        ->assertScript(<<<'JS'
            (() => {
                const buttons = document.querySelectorAll('button');
                return Array.from(buttons).some(b =>
                    b.querySelector('[class*="pencil"]') ||
                    b.getAttribute('x-on:click')?.includes('editGridMode')
                );
            })()
        JS);
});

test('the same widget can be added twice in a row', function (): void {
    $page = visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke();

    $page->script(<<<'JS'
        () => document.querySelector('button[x-on\\:click="editGridMode(true)"]').click()
    JS);
    $page->script(<<<'JS'
        () => document.querySelector('button[x-on\\:click="$tsui.open.modal(\'widget-list\')"]').click()
    JS);
    waitForElement($page, '#widget-list [x-on\\\\:click="toggleSelect(node)"]');

    $leaf = <<<'JS'
        Array.from(document.querySelectorAll('#widget-list [x-on\\:click="toggleSelect(node)"]'))
            .find((el) => ! Alpine.$data(el).node.children)
    JS;
    $count = "document.querySelectorAll('.grid-stack > .grid-stack-item').length";

    $page->script(<<<'JS'
        () => {
            const nodes = document.querySelectorAll('#widget-list [x-on\\:click="toggleSelect(node)"]');
            if (! Array.from(nodes).some((el) => ! Alpine.$data(el).node.children)) {
                nodes[0].querySelector('i').click();
            }
        }
    JS);
    waitForCondition($page, "() => !! {$leaf}");

    $before = $page->script("() => {$count}");

    $page->script("() => {$leaf}.click()");
    waitForCondition($page, "() => {$count} === {$before} + 1 && ! Alpine.\$data({$leaf}).isLoading", 12000);

    $page->script("() => {$leaf}.click()");
    waitForCondition($page, "() => {$count} === {$before} + 2 && ! Alpine.\$data({$leaf}).isLoading", 12000);

    expect($page->script("() => {$count}"))->toBe($before + 2);
});

test('a moved widget is saved with its new position', function (): void {
    $page = visit(route('dashboard'))
        ->assertRoute('dashboard')
        ->assertNoSmoke();

    $dashboard = "Alpine.\$data(document.querySelector('[x-data=\"dashboard()\"]'))";
    $count = "document.querySelectorAll('.grid-stack > .grid-stack-item').length";

    $page->script(<<<'JS'
        () => document.querySelector('button[x-on\\:click="editGridMode(true)"]').click()
    JS);
    $page->script("() => {$dashboard}.selectWidget(Object.keys({$dashboard}.\$wire.availableWidgets)[0])");
    waitForCondition($page, "() => {$count} === 1 && ! {$dashboard}.isLoading", 12000);

    $page->script("() => {$dashboard}.grid.update({$dashboard}.grid.getGridItems()[0], { y: 2 })");
    $page->script("() => {$dashboard}.save()");
    waitForCondition($page, "() => ! {$dashboard}.editGrid && ! {$dashboard}.isLoading", 12000);

    expect(
        Widget::query()
            ->where('widgetable_id', $this->user->getKey())
            ->where('dashboard_component', Dashboard::class)
            ->pluck('order_row')
            ->all()
    )->toBe([2]);
});
