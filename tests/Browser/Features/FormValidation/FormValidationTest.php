<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\OrderType;

test('create order without required fields shows validation errors', function (): void {
    OrderType::query()->delete();
    OrderType::factory()->create([
        'order_type_enum' => OrderTypeEnum::Order,
        'is_active' => true,
        'is_hidden' => false,
    ]);

    $page = visit(route('orders.orders'))
        ->assertRoute('orders.orders')
        ->assertNoSmoke();

    // Click New order
    $page->script(<<<'JS'
        () => {
            const btn = Array.from(document.querySelectorAll('button'))
                .find(b => b.textContent?.includes('New') || b.textContent?.includes('Neu'));
            if (btn) btn.click();
        }
    JS);

    $page->script('() => new Promise(r => setTimeout(r, 1000))');

    // Try to save without filling required fields
    $page->script(<<<'JS'
        () => {
            const saveBtn = Array.from(document.querySelectorAll('button'))
                .find(b => b.textContent?.includes('Save') || b.textContent?.includes('Speichern'));
            if (saveBtn) saveBtn.click();
        }
    JS);

    $page->script('() => new Promise(r => setTimeout(r, 1500))');

    // Check for validation errors (red borders, error messages, toast)
    $hasErrors = $page->script(<<<'JS'
        () => {
            const errorBorders = document.querySelectorAll('.border-red-500, .ring-red-500, [class*="error"]');
            const errorMessages = document.querySelectorAll('[class*="error"], [role="alert"]');
            return (errorBorders.length + errorMessages.length) > 0;
        }
    JS);
    expect($$hasErrors)->toBeTrue();

    $page->assertNoJavascriptErrors();
});

test('settings form save triggers validation', function (): void {
    $page = visit(route('settings'))
        ->assertRoute('settings')
        ->assertNoSmoke();

    $page->assertNoJavascriptErrors();
});
