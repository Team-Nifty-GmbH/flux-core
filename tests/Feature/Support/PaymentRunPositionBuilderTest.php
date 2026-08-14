<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Support\PaymentRunPositionBuilder;

test('a position with many documents never contains a partial document number and stays within 140 characters', function (): void {
    $builder = new PaymentRunPositionBuilder();
    $numbers = collect(range(1, 30))
        ->map(fn (int $i) => 'RE-2026-' . str_pad((string) $i, 6, '0', STR_PAD_LEFT))
        ->all();

    $purpose = $builder->purpose($numbers);

    expect(mb_strlen($purpose))->toBeLessThanOrEqual(140);

    [$list] = explode(' +', $purpose, 2);
    $listedNumbers = array_filter(explode(', ', $list));

    foreach ($listedNumbers as $listedNumber) {
        expect($numbers)->toContain($listedNumber);
    }
});

test('the count of omitted documents is stated when not all fit', function (): void {
    $builder = new PaymentRunPositionBuilder();
    $numbers = collect(range(1, 30))
        ->map(fn (int $i) => 'RE-2026-' . str_pad((string) $i, 6, '0', STR_PAD_LEFT))
        ->all();

    $purpose = $builder->purpose($numbers);

    [$list] = explode(' +', $purpose, 2);
    $listedCount = count(array_filter(explode(', ', $list)));
    $omittedCount = count($numbers) - $listedCount;

    expect($purpose)->toContain('+' . $omittedCount . ' more');
});

test('the reference is included in the tail once it is known', function (): void {
    $builder = new PaymentRunPositionBuilder();
    $numbers = collect(range(1, 30))
        ->map(fn (int $i) => 'RE-2026-' . str_pad((string) $i, 6, '0', STR_PAD_LEFT))
        ->all();

    $purpose = $builder->purpose($numbers, 'PR1-2');

    expect($purpose)->toContain('advice PR1-2')
        ->and(mb_strlen($purpose))->toBeLessThanOrEqual(140);
});

test('a position with few documents produces the plain list with no tail', function (): void {
    $builder = new PaymentRunPositionBuilder();

    $purpose = $builder->purpose(['RE-1', 'RE-2', 'RE-3'], 'PR1-2');

    expect($purpose)->toBe('RE-1, RE-2, RE-3');
});

test('the preview purpose lists the same documents as the final one once the reference lengths match', function (): void {
    $contact = Contact::factory()->create();
    $address = Address::factory()->create(['contact_id' => $contact->getKey()]);
    $orderType = OrderType::factory()->create([
        'order_type_enum' => OrderTypeEnum::Purchase,
        'is_active' => true,
        'is_hidden' => false,
    ]);
    $paymentType = PaymentType::factory()->hasAttached($this->dbTenant, relationship: 'tenants')->create();

    $orders = collect(range(1, 30))->map(fn (int $i) => Order::factory()->create([
        'tenant_id' => $this->dbTenant->getKey(),
        'contact_id' => $contact->getKey(),
        'address_invoice_id' => $address->getKey(),
        'order_type_id' => $orderType->getKey(),
        'payment_type_id' => $paymentType->getKey(),
        'price_list_id' => PriceList::factory()->create()->getKey(),
        'currency_id' => Currency::factory()->create()->getKey(),
        'language_id' => $this->defaultLanguage->getKey(),
        'iban' => 'DE89370400440532013000',
        'invoice_number' => 'RE-2026-' . str_pad((string) $i, 6, '0', STR_PAD_LEFT),
        'balance' => '-100.00',
        'total_gross_price' => '100.00',
    ]));

    $builder = new PaymentRunPositionBuilder();
    $groups = $builder->build($orders, collect());

    [$previewList] = explode(' +', $groups[0]['purpose'], 2);

    $invoiceNumbers = $orders->pluck('invoice_number')->all();
    $finalPurpose = $builder->purpose($invoiceNumbers, 'PR123-456');
    [$finalList] = explode(' +', $finalPurpose, 2);

    expect(mb_strlen(PaymentRunPositionBuilder::PLACEHOLDER_END_TO_END_ID))->toBe(mb_strlen('PR123-456'))
        ->and($previewList)->toBe($finalList)
        ->and($finalPurpose)->toContain('PR123-456');
});
