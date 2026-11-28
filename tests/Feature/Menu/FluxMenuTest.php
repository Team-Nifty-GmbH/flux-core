<?php

use FluxErp\Facades\Menu;

test('shows resources as a top level entry', function (): void {
    $menu = Menu::forGuard('web', ignorePermissions: true);

    expect(data_get($menu, 'resources.label'))->toBe('Resources')
        ->and(data_get($menu, 'resources.uri'))->toBe(route('resources.resources'));
});

// The navigation and the settings page show __(Str::headline($label)), so a label is looked up in headline case.
test('menu labels of core are translated in headline case', function (string $path, string $locale, string $expected): void {
    app()->setLocale($locale);

    expect(__(Illuminate\Support\Str::headline(data_get(Menu::all(), $path . '.label'))))->toBe($expected);
})->with([
    ['orders.children.orders', 'de', 'Alle Aufträge'],
    ['settings.children.general.children.search-settings', 'de', 'Sucheinstellungen'],
    ['settings.children.accounting.children.vat-rates', 'en', 'VAT Rates'],
    ['settings.children.general.children.mail-settings', 'de', 'E-Mail-Einstellungen'],
]);

test('menu labels are registered untranslated so they follow the user locale', function (): void {
    // The menu resolves lazily, in the locale of the first request that reads it.
    app()->setLocale('de');

    expect(data_get(Menu::all(), 'orders.children.orders.label'))->toBe('All orders');
});
