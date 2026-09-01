<?php

use FluxErp\Models\OrderPosition;
use Illuminate\Support\Facades\Blade;

function renderPrintOrderPosition(array $attributes): string
{
    $position = (new OrderPosition())->forceFill(array_merge([
        'slug_position' => '1',
        'name' => 'Test Product',
        'product_number' => 'P-100',
        'description' => null,
        'depth' => 0,
        'amount' => 1,
        'is_alternative' => false,
        'is_free_text' => false,
        'is_bundle_position' => false,
        'total_net_price' => '10.00',
        'total_gross_price' => '11.90',
        'total_base_net_price' => '10.00',
        'total_base_gross_price' => '11.90',
    ], $attributes));
    $position->setRelation('product', null);

    return Blade::render(
        '<x-flux::print.order.order-position :position="$position" :is-net="true" />',
        ['position' => $position]
    );
}

test('print order position renders a single row without description', function (): void {
    $html = renderPrintOrderPosition(['description' => null]);

    expect(substr_count($html, '<tr'))->toBe(1)
        ->and($html)->toContain('Test Product')
        ->and($html)->toContain('padding-bottom: 8px;');
});

test('print order position renders one row per description chunk', function (): void {
    $html = renderPrintOrderPosition([
        'description' => '<p>First paragraph</p><p>Second paragraph</p><ul><li>One</li><li>Two</li></ul>',
    ]);

    // 1 main row + 2 paragraph rows + 2 list item rows
    expect(substr_count($html, '<tr'))->toBe(5)
        ->and($html)->toContain('First paragraph')
        ->and($html)->toContain('Second paragraph')
        ->and($html)->toContain('<li>One</li>')
        ->and($html)->toContain('<li>Two</li>');
});

test('print order position keeps bottom padding only on the last description row', function (): void {
    $html = renderPrintOrderPosition([
        'description' => '<p>First</p><p>Last</p>',
    ]);

    expect(substr_count($html, 'padding-bottom: 8px;'))->toBe(1)
        ->and(strrpos($html, 'padding-bottom: 8px;'))->toBeGreaterThan(strpos($html, 'First'));
});
