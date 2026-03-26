<?php

use Carbon\Carbon;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Livewire\Widgets\Outstanding;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\PurchaseInvoice;
use FluxErp\Models\Tenant;
use FluxErp\States\Order\PaymentState\Open;
use FluxErp\States\Order\PaymentState\Paid;
use FluxErp\States\Order\PaymentState\PartialPaid;
use Illuminate\Support\Collection;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->currency = Currency::factory()->create([
        'is_default' => true,
    ]);

    $this->overDueDate = Carbon::now()->subDay()->toDateString();

    $this->inTimeDate = Carbon::now()->addDay()->toDateString();

    $this->orderTypeIds = resolve_static(OrderType::class, 'query')
        ->where('is_active', true)
        ->get(['id', 'order_type_enum'])
        ->filter(fn (OrderType $orderType) => ! $orderType->order_type_enum->isPurchase()
            && $orderType->order_type_enum->multiplier() > 0
        )
        ->pluck('id')
        ->toArray();
});

test('calculate sum payment state all time relations in time', function (): void {
    $paymentProps = collect([
        [
            'state' => Paid::class,
            'date' => $this->inTimeDate,
        ],
        [
            'state' => PartialPaid::class,
            'date' => $this->inTimeDate,
        ],
        [
            'state' => Open::class,
            'date' => $this->inTimeDate,
        ],
    ]);

    $orders = createData($paymentProps, $this->dbTenant, $this->currency);

    Livewire::test(Outstanding::class)
        ->call('calculateSum')
        ->assertSet('sum', createSumString($orders, $this->currency->symbol))
        ->assertSet('subValue', createSubString($orders, $this->orderTypeIds, $this->currency->symbol))
        ->assertHasNoErrors()
        ->assertOk();
});

test('calculate sum payment state all time relations over due', function (): void {
    $paymentProps = collect([
        [
            'state' => Paid::class,
            'date' => $this->overDueDate,
        ],
        [
            'state' => PartialPaid::class,
            'date' => $this->overDueDate,
        ],
        [
            'state' => Open::class,
            'date' => $this->overDueDate,
        ],
    ]);

    $orders = createData($paymentProps, $this->dbTenant, $this->currency);

    Livewire::test(Outstanding::class)
        ->call('calculateSum')
        ->assertSet('sum', createSumString($orders, $this->currency->symbol))
        ->assertSet('subValue', createSubString($orders, $this->orderTypeIds, $this->currency->symbol))
        ->assertHasNoErrors()
        ->assertOk();
});

test('calculate sum payment state open time relations all', function (): void {
    $paymentProps = collect([
        [
            'state' => Open::class,
            'date' => $this->overDueDate,
        ],
        [
            'state' => Open::class,
            'date' => $this->inTimeDate,
        ],
    ]);

    $orders = createData($paymentProps, $this->dbTenant, $this->currency);

    Livewire::test(Outstanding::class)
        ->call('calculateSum')
        ->assertSet('sum', createSumString($orders, $this->currency->symbol))
        ->assertSet('subValue', createSubString($orders, $this->orderTypeIds, $this->currency->symbol))
        ->assertHasNoErrors()
        ->assertOk();
});

test('calculate sum payment state paid time relations all', function (): void {
    $paymentProps = collect([
        [
            'state' => Paid::class,
            'date' => $this->overDueDate,
        ],
        [
            'state' => Paid::class,
            'date' => $this->inTimeDate,
        ],
    ]);

    $orders = createData($paymentProps, $this->dbTenant, $this->currency);

    Livewire::test(Outstanding::class)
        ->call('calculateSum')
        ->assertSet('sum', createSumString($orders, $this->currency->symbol))
        ->assertSet('subValue', createSubString($orders, $this->orderTypeIds, $this->currency->symbol))
        ->assertHasNoErrors()
        ->assertOk();
});

test('calculate sum payment state partial paid time relations all', function (): void {
    $paymentProps = collect([
        [
            'state' => PartialPaid::class,
            'date' => $this->overDueDate,
        ],
        [
            'state' => PartialPaid::class,
            'date' => $this->inTimeDate,
        ],
    ]);

    $orders = createData($paymentProps, $this->dbTenant, $this->currency);

    Livewire::test(Outstanding::class)
        ->call('calculateSum')
        ->assertSet('sum', createSumString($orders, $this->currency->symbol))
        ->assertSet('subValue', createSubString($orders, $this->orderTypeIds, $this->currency->symbol))
        ->assertHasNoErrors()
        ->assertOk();
});

test('calculate table empty', function (): void {
    $orders = collect();

    Livewire::test(Outstanding::class)
        ->call('calculateSum')
        ->assertSet('sum', createSumString($orders, $this->currency->symbol))
        ->assertSet('subValue', createSubString($orders, $this->orderTypeIds, $this->currency->symbol))
        ->assertHasNoErrors()
        ->assertOk();
});

test('redirect to orders', function (): void {
    Livewire::test(Outstanding::class)
        ->call('show')
        ->assertRedirect(route('orders.orders'))
        ->assertHasNoErrors()
        ->assertOk();
});

test('redirect to over due', function (): void {
    Livewire::test(Outstanding::class)
        ->call('showOverdue')
        ->assertRedirect(route('accounting.payment-reminders'))
        ->assertHasNoErrors()
        ->assertOk();
});

test('renders successfully', function (): void {
    createData(collect(), $this->dbTenant, $this->currency);

    Livewire::test(Outstanding::class)
        ->assertOk();
});

function createData(Collection $paymentProps, Tenant $tenant, Currency $currency): Collection
{
    $orders = collect();

    $contact = Contact::factory()->create([
        'tenant_id' => $tenant->getKey(),
    ]);

    $address = Address::factory()->create([
        'tenant_id' => $tenant->getKey(),
        'contact_id' => $contact->id,
    ]);

    $priceList = PriceList::factory()->create();

    $language = Language::factory()->create();

    $orderType = OrderType::factory()->create([
        'tenant_id' => $tenant->getKey(),
        'order_type_enum' => OrderTypeEnum::Order,
    ]);

    $paymentType = PaymentType::factory()
        ->hasAttached(factory: $tenant, relationship: 'tenants')
        ->create([
            'is_default' => false,
            'is_direct_debit' => true,
        ]);

    $invoice = PurchaseInvoice::factory()->create([
        'invoice_number' => Str::uuid()->toString(),
        'invoice_date' => Carbon::now()->subDays(3)->toDateString(),
    ]);

    foreach ($paymentProps as $paymentProp) {
        $orders->push(
            Order::factory()->create(
                [
                    'tenant_id' => $tenant->getKey(),
                    'language_id' => $language->id,
                    'invoice_date' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'order_type_id' => $orderType->id,
                    'payment_type_id' => $paymentType->id,
                    'price_list_id' => $priceList->id,
                    'currency_id' => $currency->id,
                    'address_invoice_id' => $address->id,
                    'address_delivery_id' => $address->id,
                    'is_locked' => false,
                    'total_gross_price' => 100,
                    'balance' => 100,
                    'payment_state' => data_get($paymentProp, 'state'),
                    'payment_reminder_next_date' => data_get($paymentProp, 'date'),
                ])
        );
    }

    return $orders;
}

function createSubString(Collection $orders, array $orderTypeIds, string $currencySymbol): string
{
    return '<span class="text-red-600">'
    . Number::abbreviate(
        $orders
            ->where('balance', '>', 0)
            ->where('payment_state', '!=', Paid::$name)
            ->whereNotNull('invoice_number')
            ->whereNotNull('invoice_date')
            ->where('payment_reminder_next_date', '<=', now()->endOfDay()->toDate())
            ->where(
                'order_type_id',
                $orderTypeIds
            )
            ->sum('balance'),
        2)
    . ' ' . $currencySymbol . ' ' . __('Overdue')
    . '</span>';
}

function createSumString(Collection $orders, string $currencySymbol): string
{
    return Number::abbreviate(
        $orders
            ->whereNotNull('invoice_date')
            ->whereNotNull('invoice_number')
            ->where('payment_state', '!=', Paid::$name)
            ->sum('balance'),
        2
    ) . ' ' . $currencySymbol;
}
