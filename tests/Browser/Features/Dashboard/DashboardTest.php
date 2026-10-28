<?php

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

    $counts = $page->script(<<<'JS'
        () => new Promise((resolve, reject) => {
            const timeout = setTimeout(() => reject(new Error('widget was not added')), 15000);
            const count = () => document.querySelectorAll('.grid-stack > .grid-stack-item').length;
            const leaf = () => {
                const nodes = Array.from(
                    document.querySelectorAll('#widget-list [x-on\\:click="toggleSelect(node)"]')
                );
                const found = nodes.find((el) => !Alpine.$data(el).node.children);
                if (!found) {
                    nodes[0].querySelector('i').click();
                }

                return found;
            };
            const clickLeafAndWait = (expected) => new Promise((done) => {
                const tryClick = () => {
                    const el = leaf();
                    if (!el) {
                        setTimeout(tryClick, 200);
                        return;
                    }
                    el.click();
                    const check = () => count() >= expected ? done() : setTimeout(check, 200);
                    check();
                };
                tryClick();
            });
            const before = count();

            clickLeafAndWait(before + 1)
                .then(() => new Promise((wait) => setTimeout(wait, 1000)))
                .then(() => clickLeafAndWait(before + 2))
                .then(() => {
                    clearTimeout(timeout);
                    resolve([before, count()]);
                });
        })
    JS);

    expect($counts[1])->toBe($counts[0] + 2);
});
