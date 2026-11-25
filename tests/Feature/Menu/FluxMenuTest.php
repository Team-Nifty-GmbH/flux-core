<?php

use FluxErp\Facades\Menu;

test('shows resources as a top level entry', function (): void {
    $menu = Menu::forGuard('web', ignorePermissions: true);

    expect(data_get($menu, 'resources.label'))->toBe('Resources')
        ->and(data_get($menu, 'resources.uri'))->toBe(route('resources.resources'));
});
