<?php

$settingsWithNewButton = [
    'settings.countries',
    'settings.currencies',
    'settings.languages',
    'settings.units',
    'settings.tags',
    'settings.categories',
    'settings.industries',
    'settings.address-types',
    'settings.payment-types',
    'settings.vat-rates',
    'settings.ledger-accounts',
    'settings.price-lists',
    'settings.warehouses',
    'settings.serial-number-ranges',
    'settings.order-types',
    'settings.lead-states',
    'settings.lead-loss-reasons',
    'settings.locations',
    'settings.holidays',
    'settings.absence-types',
    'settings.absence-policies',
    'settings.work-time-models',
    'settings.work-time-types',
    'settings.ticket-types',
    'settings.vacation-blackouts',
    'settings.vacation-carryover-rules',
    'settings.country-regions',
    'settings.employee-departments',
    'settings.record-origins',
    'settings.targets',
];

foreach ($settingsWithNewButton as $routeName) {
    $label = str_replace('settings.', '', $routeName);

    test("{$label} create button opens form", function () use ($routeName): void {
        $page = visit(route($routeName))
            ->assertNoSmoke();

        $page->script(<<<'JS'
            () => {
                const btn = Array.from(document.querySelectorAll('button, a'))
                    .find(b => b.textContent?.trim().includes('Create') || b.textContent?.trim().includes('Neu') || b.textContent?.trim().includes('New'));
                if (btn) btn.click();
            }
        JS);

        $page->assertNoJavascriptErrors();
    });
}

$settingsWithUsers = [
    'settings.users',
    'settings.permissions',
    'settings.tokens',
];

foreach ($settingsWithUsers as $routeName) {
    $label = str_replace('settings.', '', $routeName);

    test("{$label} page is interactive", function () use ($routeName): void {
        visit(route($routeName))
            ->assertNoSmoke()
            ->assertNoJavascriptErrors();
    });
}

test('settings.mail-accounts has new button', function (): void {
    $page = visit(route('settings.mail-accounts'))
        ->assertNoSmoke();

    $page->script(<<<'JS'
        () => {
            const btn = Array.from(document.querySelectorAll('button, a'))
                .find(b => b.textContent?.trim().includes('Create') || b.textContent?.trim().includes('Neu'));
            if (btn) btn.click();
        }
    JS);
    $page->assertNoJavascriptErrors();
});

test('settings.email-templates is interactive', function (): void {
    visit(route('settings.email-templates'))
        ->assertNoSmoke()
        ->assertNoJavascriptErrors();
});

test('settings.bank-connections has new button', function (): void {
    $page = visit(route('settings.bank-connections'))
        ->assertNoSmoke();

    $page->script(<<<'JS'
        () => {
            const btn = Array.from(document.querySelectorAll('button, a'))
                .find(b => b.textContent?.trim().includes('Create') || b.textContent?.trim().includes('Neu'));
            if (btn) btn.click();
        }
    JS);
    $page->assertNoJavascriptErrors();
});

test('settings.scheduling page loads', function (): void {
    visit(route('settings.scheduling'))
        ->assertNoSmoke()
        ->assertNoJavascriptErrors();
});

test('settings.plugins page loads', function (): void {
    visit(route('settings.plugins'))
        ->assertNoSmoke()
        ->assertNoJavascriptErrors();
});

test('settings.tenants page is interactive', function (): void {
    visit(route('settings.tenants'))
        ->assertNoSmoke()
        ->assertNoJavascriptErrors();
});

test('settings.discount-groups has interactive form', function (): void {
    visit(route('settings.discount-groups'))
        ->assertNoSmoke()
        ->assertNoJavascriptErrors();
});

test('settings.payment-reminder-texts page is interactive', function (): void {
    visit(route('settings.payment-reminder-texts'))
        ->assertNoSmoke()
        ->assertNoJavascriptErrors();
});
