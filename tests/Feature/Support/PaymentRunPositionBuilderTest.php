<?php

use FluxErp\Actions\PaymentRun\CreatePaymentRun;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Contact;
use FluxErp\Models\Order;
use FluxErp\Support\PaymentRunPositionBuilder;

test('a position with many documents never contains a partial document number and stays within 140 characters', function (): void {
    $builder = new PaymentRunPositionBuilder();
    $numbers = collect(range(1, 30))
        ->map(fn (int $i) => 'RE-2026-' . str_pad((string) $i, 6, '0', STR_PAD_LEFT))
        ->all();

    $purpose = $builder->purpose($numbers);

    expect(mb_strlen($purpose))->toBeLessThanOrEqual(140);

    [$list] = explode(' (+', $purpose, 2);
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

    [$list] = explode(' (+', $purpose, 2);
    $listedCount = count(array_filter(explode(', ', $list)));
    $omittedCount = count($numbers) - $listedCount;

    expect($purpose)->toContain('(+' . $omittedCount . ')');
});

test('the reference is included in the tail once it is known', function (): void {
    $builder = new PaymentRunPositionBuilder();
    $numbers = collect(range(1, 30))
        ->map(fn (int $i) => 'RE-2026-' . str_pad((string) $i, 6, '0', STR_PAD_LEFT))
        ->all();

    $purpose = $builder->purpose($numbers, 'PR1-2');

    expect($purpose)->toEndWith(' PR1-2')
        ->and(mb_strlen($purpose))->toBeLessThanOrEqual(140);
});

test('the purpose stays within 140 characters with a three digit count and the longest possible reference', function (): void {
    $numbers = collect(range(1, 250))
        ->map(fn (int $i) => 'RE-2026-' . str_pad((string) $i, 6, '0', STR_PAD_LEFT))
        ->all();
    $endToEndId = str_repeat('9', PaymentRunPositionBuilder::END_TO_END_ID_MAX_LENGTH);

    $purpose = (new PaymentRunPositionBuilder())->purpose($numbers, $endToEndId);

    expect(mb_strlen($purpose))->toBeLessThanOrEqual(140)
        ->and($purpose)->toEndWith(' ' . $endToEndId);
});

test('a document number listed twice appears once in the purpose', function (): void {
    $purpose = (new PaymentRunPositionBuilder())->purpose(['RE-1', 'RE-2', 'RE-1']);

    expect($purpose)->toBe('RE-1, RE-2');
});

test('a position with few documents produces the plain list with no tail', function (): void {
    $builder = new PaymentRunPositionBuilder();

    $purpose = $builder->purpose(['RE-1', 'RE-2', 'RE-3'], 'PR1-2');

    expect($purpose)->toBe('RE-1, RE-2, RE-3');
});

test('the preview purpose lists the same documents as the stored one even though the real reference is shorter than the budget', function (): void {
    $contactId = Contact::factory()->create()->getKey();

    $orders = collect(range(1, 30))->map(fn (int $i) => createPaymentRunOrder(OrderTypeEnum::Purchase, '-100.00', [
        'contact_id' => $contactId,
        'iban' => 'DE89370400440532013000',
        'invoice_number' => sprintf('A%02d', $i),
    ]));

    $builder = new PaymentRunPositionBuilder();
    $groups = $builder->build($orders, collect());
    [$previewList] = explode(' (+', $groups[0]['purpose'], 2);

    $run = CreatePaymentRun::make([
        'payment_run_type_enum' => 'money_transfer',
        'positions' => [
            [
                'contact_id' => $contactId,
                'iban' => 'DE89370400440532013000',
                'orders' => $orders
                    ->map(fn (Order $order) => ['order_id' => $order->getKey(), 'amount' => -100.00])
                    ->all(),
            ],
        ],
    ])->validate()->execute();

    $position = $run->positions()->sole();

    expect(mb_strlen($position->end_to_end_id))
        ->toBeLessThan(mb_strlen(PaymentRunPositionBuilder::PLACEHOLDER_END_TO_END_ID));

    [$finalList] = explode(' (+', $position->purpose, 2);

    expect($previewList)->toBe($finalList);
});

test('recap clamps every row to its own balance and caps credit notes against the payable', function (
    array $rows,
    array $expectedAmounts,
    array $expectedCappedFrom
): void {
    $recapped = app(PaymentRunPositionBuilder::class)->recap(array_map(
        fn (array $row) => [
            'amount' => $row[0],
            'max_amount' => $row[1],
            'is_credit_note' => $row[2],
            'multiplier' => $row[2] ? 1 : -1,
            'capped_from' => null,
        ],
        $rows
    ));

    expect(array_column($recapped, 'amount'))->toBe($expectedAmounts)
        ->and(array_column($recapped, 'capped_from'))->toBe($expectedCappedFrom);
})->with([
    'a smaller credit note is deducted in full' => [
        [['-1000.00', '1000.00', false], ['200.00', '200.00', true]],
        ['-1000.00', '200.00'],
        [null, null],
    ],
    'a credit note larger than the invoice is capped' => [
        [['-300.00', '300.00', false], ['500.00', '500.00', true]],
        ['-300.00', '300.00'],
        [null, '500.00'],
    ],
    'a second credit note beyond the payable drops to zero' => [
        [['-300.00', '300.00', false], ['300.00', '300.00', true], ['150.00', '150.00', true]],
        ['-300.00', '300.00', '0.00'],
        [null, null, '150.00'],
    ],
    'an invoice above its own balance is clamped to that balance' => [
        [['9999.00', '500.00', false]],
        ['-500.00'],
        [null],
    ],
    'a credit note above its own balance is clamped to that balance, not the payable sum' => [
        [['-1000.00', '1000.00', false], ['-500.00', '500.00', false], ['5000.00', '200.00', true]],
        ['-1000.00', '-500.00', '200.00'],
        [null, null, null],
    ],
    'a positive value typed into a payable row stays negative' => [
        [['50.00', '500.00', false]],
        ['-50.00'],
        [null],
    ],
    'a negative value typed into a credit note row stays positive' => [
        [['-1000.00', '1000.00', false], ['-200.00', '500.00', true]],
        ['-1000.00', '200.00'],
        [null, null],
    ],
]);
