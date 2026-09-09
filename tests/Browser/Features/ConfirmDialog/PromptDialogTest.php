<?php

use FluxErp\Models\Product;
use FluxErp\Models\Tag;

beforeEach(function (): void {
    $this->product = Product::factory()->create([
        'name' => 'Prompt Dialog Product',
        'is_active' => true,
    ]);
});

test('creating a tag through the prompt dialog works', function (): void {
    $page = visit(route('products.id', ['id' => $this->product->getKey()]))
        ->assertNoSmoke();

    // Open the prompt dialog via the green plus next to the tag select.
    $page->script(<<<'JS'
        () => {
            [...document.querySelectorAll('button')]
                .find((button) =>
                    (button.getAttribute('wire:click') ?? '').includes('addTag'),
                )
                .click();
        }
    JS);

    waitForElement($page, '#prompt-value', 10000);

    $page->script(<<<'JS'
        () => {
            const input = document.getElementById('prompt-value');
            input.value = 'Prompt Repro Tag';
            input.dispatchEvent(new Event('input', { bubbles: true }));
        }
    JS);

    $page->script(<<<'JS'
        () => {
            [...document.querySelectorAll('button')]
                .find((button) => button.textContent.trim() === 'Save')
                .click();
        }
    JS);

    $page->wait(1.5)->assertNoJavascriptErrors();

    expect(Tag::query()->where('name', 'Prompt Repro Tag')->exists())->toBeTrue();
});
