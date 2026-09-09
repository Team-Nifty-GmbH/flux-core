<?php

use FluxErp\Tests\Fixtures\Livewire\CheckboxTreeFixture;

test('the checkbox tree keeps writing to the bound property after unchecking', function (): void {
    $page = visitLivewire(CheckboxTreeFixture::class)->assertNoSmoke();

    $page->script('() => new Promise(r => setTimeout(r, 1000))');

    $read = <<<'JS'
        () => window.Livewire.all()[0].$wire.selected
    JS;

    $click = <<<'JS'
        (value) => {
            const box = document.querySelector(
                '[data-testid="checkbox-tree-fixture"] input[type=checkbox][value="' + value + '"]'
            );
            box.click();

            return new Promise(r => setTimeout(r, 300));
        }
    JS;

    $page->script($click, ['alpha']);
    expect($page->script($read))->toContain('alpha');

    $page->script($click, ['alpha']);
    expect($page->script($read))->not->toContain('alpha');

    $page->script($click, ['beta']);
    expect($page->script($read))->toContain('beta');
});
