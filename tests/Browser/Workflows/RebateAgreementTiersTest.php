<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Contact;
use FluxErp\Models\OrderType;
use FluxErp\Models\RebateAgreement;

test('tiers are added, filled and removed in the browser and saved', function (): void {
    $contact = Contact::factory()
        ->hasAttached($this->dbTenant, relationship: 'tenants')
        ->create();
    $orderType = OrderType::factory()->create([
        'order_type_enum' => OrderTypeEnum::Refund,
        'is_active' => true,
    ]);

    $page = visit('/contacts/contacts/' . $contact->getKey() . '?tab=contact.accounting')
        ->assertNoSmoke();

    $page->click('Rebate Agreements');

    $component = <<<'JS'
        Livewire.all().find(c => c.name === 'contact.accounting.rebate-agreements')
    JS;

    waitForCondition($page, "() => !!{$component}");

    $page->script("() => {$component}.\$wire.edit()");

    waitForCondition($page, "() => {$component}.\$wire.rebateAgreementForm.contact_id === {$contact->getKey()}");

    $page->script(<<<'JS'
        () => Array.from(document.querySelectorAll('button'))
            .find(button => /Add Tier|Stufe hinzufügen/.test(button.textContent))
            .click()
    JS);
    $page->script(<<<'JS'
        () => Array.from(document.querySelectorAll('button'))
            .find(button => /Add Tier|Stufe hinzufügen/.test(button.textContent))
            .click()
    JS);

    waitForCondition($page, "() => document.querySelectorAll('input[x-model\\\\.number=\"tier.from_volume\"]').length === 2");

    $page->script(<<<'JS'
        () => {
            const fromVolumes = document.querySelectorAll('input[x-model\\.number="tier.from_volume"]');
            const percentages = document.querySelectorAll('input[x-model\\.number="tier.percentage"]');

            for (const [index, values] of [[10000, 1], [50000, 3]].entries()) {
                fromVolumes[index].value = values[0];
                fromVolumes[index].dispatchEvent(new Event('input', { bubbles: true }));
                percentages[index].value = values[1];
                percentages[index].dispatchEvent(new Event('input', { bubbles: true }));
            }
        }
    JS);

    $page->script(<<<'JS'
        () => {
            const firstRow = document.querySelector('template[x-for*="rebateAgreementForm.tiers"]')
                .nextElementSibling;

            Array.from(firstRow.querySelectorAll('button')).at(-1).click();
        }
    JS);

    waitForCondition($page, "() => {$component}.\$wire.rebateAgreementForm.tiers.length === 1");

    $page->script(<<<JS
        () => {
            const wire = {$component}.\$wire;
            wire.rebateAgreementForm.order_type_id = {$orderType->getKey()};
            wire.rebateAgreementForm.period_start = '2025-01-01';
            wire.rebateAgreementForm.period_end = '2025-12-31';
            wire.save();
        }
    JS);

    waitForCondition($page, "() => {$component}.\$wire.rebateAgreementForm.id !== null", 10000);

    $agreement = RebateAgreement::query()->where('contact_id', $contact->getKey())->first();

    expect($agreement->tiers)->toHaveCount(1)
        ->and(data_get($agreement->tiers, '0.from_volume'))->toEqual(50000)
        ->and(bcround(data_get($agreement->tiers, '0.percentage'), 2))->toBe('0.03');
});
