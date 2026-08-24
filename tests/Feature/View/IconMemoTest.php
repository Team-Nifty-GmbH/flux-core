<?php

use FluxErp\View\Components\Icon;
use Illuminate\Support\Facades\Blade;
use TallStackUi\Components\Icon\Component;

test('the icon alias resolves to the memoizing component', function (): void {
    expect(Blade::getClassComponentAliases()['icon'] ?? null)->toBe(Icon::class);
});

test('the memoized icon renders the same markup as TallStackUi', function (): void {
    $memoized = Blade::render('<x-icon name="check" />');
    Blade::component(Component::class, 'tsui-icon');
    $original = Blade::render('<x-tsui-icon name="check" />');

    expect(trim($memoized))->toBe(trim($original))
        ->and($memoized)->toContain('<svg');
});

test('the memo is keyed per icon', function (): void {
    $check = Blade::render('<x-icon name="check" />');
    $again = Blade::render('<x-icon name="check" />');
    $trash = Blade::render('<x-icon name="trash" />');

    expect($again)->toBe($check)
        ->and($trash)->not->toBe($check);
});
