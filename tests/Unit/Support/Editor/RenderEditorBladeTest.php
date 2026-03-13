<?php

use FluxErp\Support\Editor\EditorManager;

beforeEach(function (): void {
    EditorManager::clearVariables();
});

test('render_editor_blade resolves new-format id to expression', function (): void {
    EditorManager::mergeVariables([
        'Name' => '$order->name',
    ], FluxErp\Models\Order::class);

    $morphAlias = morph_alias(FluxErp\Models\Order::class);
    $order = new stdClass();
    $order->name = 'Test-Value-123';

    $html = '<p>Nr: <span data-type="blade-variable" data-value="' . $morphAlias . '.name" data-label="Name">Name</span></p>';

    $result = render_editor_blade($html, ['order' => $order]);

    expect((string) $result)->toContain('Test-Value-123');
});

test('render_editor_blade falls back to raw expression for old-format data', function (): void {
    $order = new stdClass();
    $order->name = 'Old-Format-456';

    $html = '<p>Nr: <span data-type="blade-variable" data-value="$order->name" data-label="Name">Name</span></p>';

    $result = render_editor_blade($html, ['order' => $order]);

    expect((string) $result)->toContain('Old-Format-456');
});

test('render_editor_blade returns empty HtmlString for null input', function (): void {
    $result = render_editor_blade(null);

    expect((string) $result)->toBe('');
});

test('render_editor_blade preserves unresolvable variables', function (): void {
    $html = '<p><span data-type="blade-variable" data-value="$unknown->field" data-label="Unknown">Unknown</span></p>';

    $result = render_editor_blade($html, []);

    expect((string) $result)->toContain('data-type="blade-variable"');
});
