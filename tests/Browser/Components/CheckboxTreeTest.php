<?php

use FluxErp\Tests\Fixtures\Livewire\CheckboxTreeFixture;

function clickBox(int $index): string
{
    return <<<JS
        () => {
            document.querySelectorAll(
                '[data-testid="checkbox-tree-fixture"] input[type=checkbox]'
            )[{$index}].click();

            return new Promise(r => setTimeout(r, 400));
        }
    JS;
}

test('the checkbox tree keeps writing to the bound property after unchecking', function (): void {
    $page = visitLivewire(CheckboxTreeFixture::class)->assertNoSmoke();

    $page->script('() => new Promise(r => setTimeout(r, 1500))');

    $count = <<<'JS'
        () => document.querySelectorAll(
            '[data-testid="checkbox-tree-fixture"] input[type=checkbox]'
        ).length
    JS;

    expect($page->script($count))->toBe(3);

    $read = <<<'JS'
        () => JSON.stringify(
            window.Livewire.all().map((component) => component.get('selected'))
        )
    JS;

    expect($page->script($read))->toBe('[[]]');

    $page->script(clickBox(0));
    expect($page->script($read))->toBe('[["alpha"]]');

    $page->script(clickBox(0));
    expect($page->script($read))->toBe('[[]]');

    $page->script(clickBox(1));
    expect($page->script($read))->toBe('[["beta"]]');
});
