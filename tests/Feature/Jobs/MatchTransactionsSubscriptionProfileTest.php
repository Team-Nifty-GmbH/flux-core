<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Invokable\ProcessSubscriptionOrder;
use FluxErp\Jobs\Accounting\MatchTransactionsWithOrderJob;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\Tenant;
use FluxErp\Models\Transaction;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

beforeEach(function (): void {
    $this->tenant = Tenant::factory()->create();

    $this->currency = Currency::factory()->create([
        'is_default' => true,
    ]);

    $this->language = Language::factory()->create([
        'is_default' => true,
    ]);

    $this->priceList = PriceList::factory()->create([
        'is_default' => true,
    ]);

    $this->paymentType = PaymentType::factory()
        ->hasAttached(factory: $this->tenant, relationship: 'tenants')
        ->create([
            'is_default' => true,
        ]);

    $this->contact = Contact::factory()
        ->hasAttached(factory: $this->tenant, relationship: 'tenants')
        ->create();

    $this->address = Address::factory()->create([
        'contact_id' => $this->contact->getKey(),
        'is_main_address' => true,
    ]);

    $this->purchaseSubscriptionOrderType = OrderType::factory()
        ->hasAttached(factory: $this->tenant, relationship: 'tenants')
        ->create([
            'order_type_enum' => OrderTypeEnum::PurchaseSubscription,
            'is_active' => true,
        ]);

    $this->targetOrderType = OrderType::factory()
        ->hasAttached(factory: $this->tenant, relationship: 'tenants')
        ->create([
            'order_type_enum' => OrderTypeEnum::Purchase,
            'is_active' => true,
        ]);
});

function createSubscriptionContract(
    string $orderNumber,
    ?string $iban = null,
    ?string $paymentPurposePattern = null,
): Order {
    return Order::factory()->create([
        'tenant_id' => test()->tenant->getKey(),
        'contact_id' => test()->contact->getKey(),
        'address_invoice_id' => test()->address->getKey(),
        'order_type_id' => test()->purchaseSubscriptionOrderType->getKey(),
        'currency_id' => test()->currency->getKey(),
        'language_id' => test()->language->getKey(),
        'price_list_id' => test()->priceList->getKey(),
        'payment_type_id' => test()->paymentType->getKey(),
        'parent_id' => null,
        'order_number' => $orderNumber,
        'iban' => $iban,
        'payment_purpose_pattern' => $paymentPurposePattern,
        'system_delivery_date' => '2026-06-01',
        'system_delivery_date_end' => null,
    ]);
}

function generateOpenChild(Order $contract, string $deliveryDate): Order
{
    $contract->update(['system_delivery_date' => $deliveryDate]);

    (new ProcessSubscriptionOrder())($contract->getKey(), test()->targetOrderType->getKey());

    $child = $contract->createdOrders()->latest('id')->first();
    $child->update(['balance' => 1500]);

    return $child;
}

test('transaction matching suggests oldest open child via contract iban profile', function (): void {
    $contract = createSubscriptionContract('K-100', iban: 'DE02120300000000202051');

    $juneChild = generateOpenChild($contract, '2026-06-01');
    $juneChild->update(['system_delivery_date_end' => '2026-06-30']);
    $julyChild = generateOpenChild($contract, '2026-07-01');

    $transaction = Transaction::factory()->create([
        'value_date' => now(),
        'booking_date' => now(),
        'amount' => -1500,
        'balance' => -1500,
        'purpose' => 'MIETE JULI',
        'counterpart_iban' => 'DE02120300000000202051',
        'is_ignored' => false,
    ]);

    (new MatchTransactionsWithOrderJob([$transaction->getKey()]))->handle();

    $assignment = $transaction->refresh()->orderTransactions()->first();
    expect($assignment)->not->toBeNull();
    expect($assignment->order_id)->toBe($juneChild->getKey());
    expect($assignment->is_accepted)->toBeFalse();
});

test('purpose pattern disambiguates two contracts with the same creditor iban', function (): void {
    $iban = 'DE02120300000000202051';

    $musterstrContract = createSubscriptionContract('K-200', iban: $iban, paymentPurposePattern: 'Musterstr. 1');
    $beispielwegContract = createSubscriptionContract('K-300', iban: $iban, paymentPurposePattern: 'Beispielweg 2');

    generateOpenChild($musterstrContract, '2026-07-01');
    $beispielwegChild = generateOpenChild($beispielwegContract, '2026-07-01');

    $transaction = Transaction::factory()->create([
        'value_date' => now(),
        'booking_date' => now(),
        'amount' => -1500,
        'balance' => -1500,
        'purpose' => 'Miete Beispielweg 2 Juli',
        'counterpart_iban' => $iban,
        'is_ignored' => false,
    ]);

    (new MatchTransactionsWithOrderJob([$transaction->getKey()]))->handle();

    expect($transaction->refresh()->orderTransactions()->sole()->order_id)
        ->toBe($beispielwegChild->getKey());
});

test('subscription contract itself never receives an assignment', function (): void {
    $contract = createSubscriptionContract('K-400', iban: 'DE02120300000000202051');

    $transaction = Transaction::factory()->create([
        'value_date' => now(),
        'booking_date' => now(),
        'amount' => -1500,
        'balance' => -1500,
        'purpose' => 'MIETE JULI',
        'counterpart_name' => 'Non Matching Name',
        'counterpart_iban' => 'DE02120300000000202051',
        'is_ignored' => false,
    ]);

    (new MatchTransactionsWithOrderJob([$transaction->getKey()]))->handle();

    expect($transaction->refresh()->orderTransactions()->count())->toBe(0);
});
