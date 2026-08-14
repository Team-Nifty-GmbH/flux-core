<?php

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Enums\PaymentRunTypeEnum;
use FluxErp\Enums\SepaMandateTypeEnum;
use FluxErp\Livewire\Accounting\PaymentRunPreview;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\ContactBankConnection;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentRun;
use FluxErp\Models\PaymentRunPosition;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\SepaMandate;
use FluxErp\States\Order\PaymentState\InOpenPaymentRun;
use FluxErp\States\Order\PaymentState\Open;
use Illuminate\Support\Facades\DB;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

function toastTypesOf(Testable $component): array
{
    return collect($component->effects['dispatches'] ?? [])
        ->where('name', 'ts-ui:toast')
        ->pluck('params.type')
        ->all();
}

beforeEach(function (): void {
    $this->contact = Contact::factory()->create();

    $this->address = Address::factory()->create([
        'contact_id' => $this->contact->id,
        'name' => 'Test Customer',
        'is_main_address' => true,
    ]);

    $this->paymentType = PaymentType::factory()->create([
        'is_direct_debit' => false,
        'requires_manual_transfer' => true,
    ]);

    $this->priceList = PriceList::factory()->create();

    $this->currency = Currency::factory()->create();

    $this->orderType = OrderType::factory()->create([
        'order_type_enum' => collect(OrderTypeEnum::cases())
            ->first(fn ($case) => $case->multiplier() < 0),
        'is_active' => true,
        'is_hidden' => false,
    ]);

    $this->purchaseType = OrderType::factory()->create([
        'order_type_enum' => OrderTypeEnum::Purchase,
        'is_active' => true,
        'is_hidden' => false,
    ]);

    $this->refundType = OrderType::factory()->create([
        'order_type_enum' => OrderTypeEnum::PurchaseRefund,
        'is_active' => true,
        'is_hidden' => false,
    ]);

    $this->orders = Order::factory()->count(2)->create([
        'tenant_id' => $this->dbTenant->id,
        'contact_id' => $this->contact->id,
        'order_type_id' => $this->orderType->id,
        'payment_type_id' => $this->paymentType->id,
        'address_invoice_id' => $this->address->id,
        'price_list_id' => $this->priceList->id,
        'currency_id' => $this->currency->id,
    ]);

    $this->orders[0]->update([
        'invoice_number' => 'INV-001',
        'balance' => -100.50,
        'total_gross_price' => -100.50,
    ]);

    $this->orders[1]->update([
        'invoice_number' => 'INV-002',
        'balance' => -250.75,
        'total_gross_price' => -250.75,
    ]);
});

test('orders of the same recipient end up in one group', function (): void {
    $first = createPayableOrder($this, $this->purchaseType, '-1000.00');
    $second = createPayableOrder($this, $this->purchaseType, '-500.00', $first->contact_id);

    session(['payment_run_preview_orders' => [$first->getKey(), $second->getKey()]]);
    session(['payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer]);

    Livewire::test(PaymentRunPreview::class)
        ->assertCount('groups', 1)
        ->assertSet('groups.0.amount', '-1500.00');
});

test('an open credit note of the same contact is suggested and deducted', function (): void {
    $invoice = createPayableOrder($this, $this->purchaseType, '-1000.00');
    $creditNote = createPayableOrder($this, $this->refundType, '200.00', $invoice->contact_id);

    session(['payment_run_preview_orders' => [$invoice->getKey()]]);
    session(['payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer]);

    $component = Livewire::test(PaymentRunPreview::class);

    expect(data_get($component->get('groups'), '0.amount'))->toBe('-800.00')
        ->and(data_get($component->get('groups'), '0.orders.1.id'))->toBe($creditNote->getKey())
        ->and(data_get($component->get('groups'), '0.orders.1.is_suggested'))->toBeTrue();
});

test('a credit note larger than the invoices is capped', function (): void {
    $invoice = createPayableOrder($this, $this->purchaseType, '-300.00');
    $creditNote = createPayableOrder($this, $this->refundType, '500.00', $invoice->contact_id);

    session(['payment_run_preview_orders' => [$invoice->getKey()]]);
    session(['payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer]);

    $component = Livewire::test(PaymentRunPreview::class);

    expect(data_get($component->get('groups'), '0.amount'))->toBe('0.00')
        ->and(data_get($component->get('groups'), '0.orders.1.amount'))->toBe('300.00')
        ->and(data_get($component->get('groups'), '0.orders.1.capped_from'))->toBe('500.00');
});

test('a second credit note beyond the payable stays visible with a zero amount', function (): void {
    $invoice = createPayableOrder($this, $this->purchaseType, '-300.00');
    $firstCreditNote = createPayableOrder($this, $this->refundType, '300.00', $invoice->contact_id);
    $secondCreditNote = createPayableOrder($this, $this->refundType, '150.00', $invoice->contact_id);

    session(['payment_run_preview_orders' => [$invoice->getKey()]]);
    session(['payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer]);

    $component = Livewire::test(PaymentRunPreview::class);

    $rows = collect($component->get('groups'))->flatMap(fn (array $group) => $group['orders'])->keyBy('id');

    expect($rows[$firstCreditNote->getKey()]['amount'])->toBe('300.00')
        ->and($rows[$secondCreditNote->getKey()]['amount'])->toBe('0.00')
        ->and($rows[$secondCreditNote->getKey()]['capped_from'])->toBe('150.00');
});

test('a manual amount above the payable is clamped', function (): void {
    $invoice = createPayableOrder($this, $this->purchaseType, '-300.00');
    $creditNote = createPayableOrder($this, $this->refundType, '500.00', $invoice->contact_id);

    session(['payment_run_preview_orders' => [$invoice->getKey()]]);
    session(['payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer]);

    $component = Livewire::test(PaymentRunPreview::class);
    $groupKey = $component->get('groups')[0]['key'];

    $component->call('applyAmount', $groupKey, $creditNote->getKey(), '500.00');

    expect(data_get($component->get('groups'), '0.orders.1.amount'))->toBe('300.00')
        ->and(data_get($component->get('groups'), '0.amount'))->toBe('0.00');
});

test('an amount above the invoice own balance is clamped to that balance', function (): void {
    $invoice = createPayableOrder($this, $this->purchaseType, '-500.00');

    session(['payment_run_preview_orders' => [$invoice->getKey()]]);
    session(['payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer]);

    $component = Livewire::test(PaymentRunPreview::class);
    $groupKey = $component->get('groups')[0]['key'];

    $component->call('applyAmount', $groupKey, $invoice->getKey(), '9999.00');

    expect(data_get($component->get('groups'), '0.orders.0.amount'))->toBe('-500.00')
        ->and(data_get($component->get('groups'), '0.amount'))->toBe('-500.00');
});

test('an amount above the credit note own balance is clamped to that balance, not the payable sum', function (): void {
    $invoice = createPayableOrder($this, $this->purchaseType, '-1000.00');
    $secondInvoice = createPayableOrder($this, $this->purchaseType, '-500.00', $invoice->contact_id);
    $creditNote = createPayableOrder($this, $this->refundType, '200.00', $invoice->contact_id);

    session(['payment_run_preview_orders' => [$invoice->getKey(), $secondInvoice->getKey()]]);
    session(['payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer]);

    $component = Livewire::test(PaymentRunPreview::class);
    $groupKey = $component->get('groups')[0]['key'];

    $component->call('applyAmount', $groupKey, $creditNote->getKey(), '5000.00');

    expect(data_get($component->get('groups'), '0.orders.2.amount'))->toBe('200.00')
        ->and(data_get($component->get('groups'), '0.orders.2.capped_from'))->toBeNull()
        ->and(data_get($component->get('groups'), '0.amount'))->toBe('-1300.00');
});

test('typing a positive value into a payable row keeps it negative', function (): void {
    $invoice = createPayableOrder($this, $this->purchaseType, '-500.00');

    session(['payment_run_preview_orders' => [$invoice->getKey()]]);
    session(['payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer]);

    $component = Livewire::test(PaymentRunPreview::class);
    $groupKey = $component->get('groups')[0]['key'];

    $component->call('applyAmount', $groupKey, $invoice->getKey(), '50.00');

    expect(data_get($component->get('groups'), '0.orders.0.amount'))->toBe('-50.00')
        ->and(data_get($component->get('groups'), '0.amount'))->toBe('-50.00');
});

test('typing a negative value into a credit note row keeps it positive', function (): void {
    $invoice = createPayableOrder($this, $this->purchaseType, '-1000.00');
    $creditNote = createPayableOrder($this, $this->refundType, '500.00', $invoice->contact_id);

    session(['payment_run_preview_orders' => [$invoice->getKey()]]);
    session(['payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer]);

    $component = Livewire::test(PaymentRunPreview::class);
    $groupKey = $component->get('groups')[0]['key'];

    $component->call('applyAmount', $groupKey, $creditNote->getKey(), '-200.00');

    expect(data_get($component->get('groups'), '0.orders.1.amount'))->toBe('200.00');
});

test('a payable row reports a negative amount and a credit note a positive one', function (): void {
    $invoice = createPayableOrder($this, $this->purchaseType, '-1000.00');
    $creditNote = createPayableOrder($this, $this->refundType, '200.00', $invoice->contact_id);

    session(['payment_run_preview_orders' => [$invoice->getKey()]]);
    session(['payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer]);

    $component = Livewire::test(PaymentRunPreview::class);

    $rows = collect($component->get('groups'))->flatMap(fn (array $group) => $group['orders'])->keyBy('id');

    expect($rows[$invoice->getKey()]['amount'])->toBe('-1000.00')
        ->and($rows[$creditNote->getKey()]['amount'])->toBe('200.00');
});

test('a row carries total gross price and balance with their natural sign, surviving a recap', function (): void {
    $invoice = createPayableOrder($this, $this->purchaseType, '-300.00');
    $invoice->update(['total_gross_price' => '-1000.00']);

    $creditNote = createPayableOrder($this, $this->refundType, '150.00', $invoice->contact_id);
    $creditNote->update(['total_gross_price' => '500.00']);

    session(['payment_run_preview_orders' => [$invoice->getKey()]]);
    session(['payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer]);

    $component = Livewire::test(PaymentRunPreview::class);
    $groupKey = $component->get('groups')[0]['key'];

    $rows = collect($component->get('groups'))->flatMap(fn (array $group) => $group['orders'])->keyBy('id');

    expect($rows[$invoice->getKey()]['total_gross_price'])->toBe('-1000.00')
        ->and($rows[$invoice->getKey()]['balance'])->toBe('-300.00')
        ->and($rows[$creditNote->getKey()]['total_gross_price'])->toBe('500.00')
        ->and($rows[$creditNote->getKey()]['balance'])->toBe('150.00');

    $component->call('applyAmount', $groupKey, $invoice->getKey(), '250.00');

    $rows = collect($component->get('groups'))->flatMap(fn (array $group) => $group['orders'])->keyBy('id');

    expect($rows[$invoice->getKey()]['total_gross_price'])->toBe('-1000.00')
        ->and($rows[$invoice->getKey()]['balance'])->toBe('-300.00')
        ->and($rows[$creditNote->getKey()]['total_gross_price'])->toBe('500.00')
        ->and($rows[$creditNote->getKey()]['balance'])->toBe('150.00');
});

test('the group total equals the sum of the signed row amounts', function (): void {
    $invoice = createPayableOrder($this, $this->purchaseType, '-1000.00');
    createPayableOrder($this, $this->refundType, '200.00', $invoice->contact_id);

    session(['payment_run_preview_orders' => [$invoice->getKey()]]);
    session(['payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer]);

    $component = Livewire::test(PaymentRunPreview::class);
    $group = collect($component->get('groups'))->first();

    $sum = collect($group['orders'])->reduce(fn (string $carry, array $row) => bcadd($carry, $row['amount'], 2), '0.00');

    expect($group['amount'])->toBe($sum);
});

test('the payload sent to CreatePaymentRun sums to zero or negative for a normal grouped run', function (): void {
    $invoice = createPayableOrder($this, $this->purchaseType, '-1000.00');
    createPayableOrder($this, $this->refundType, '200.00', $invoice->contact_id);

    session(['payment_run_preview_orders' => [$invoice->getKey()]]);
    session(['payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer]);

    Livewire::test(PaymentRunPreview::class)
        ->call('createPaymentRun')
        ->assertRedirect();

    $position = PaymentRun::query()->latest('id')->first()->positions()->sole();

    expect(bccomp((string) $position->amount, '0', 2))->toBeLessThanOrEqual(0);
});

test('editing a credit note above the payable still caps against it and sets capped_from', function (): void {
    $invoice = createPayableOrder($this, $this->purchaseType, '-300.00');
    $creditNote = createPayableOrder($this, $this->refundType, '500.00', $invoice->contact_id);

    session(['payment_run_preview_orders' => [$invoice->getKey()]]);
    session(['payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer]);

    $component = Livewire::test(PaymentRunPreview::class);
    $groupKey = $component->get('groups')[0]['key'];

    $component->call('applyAmount', $groupKey, $creditNote->getKey(), '500.00');

    expect(data_get($component->get('groups'), '0.orders.1.amount'))->toBe('300.00')
        ->and(data_get($component->get('groups'), '0.orders.1.capped_from'))->toBe('500.00')
        ->and(data_get($component->get('groups'), '0.amount'))->toBe('0.00');
});

test('apply balance amount on a capped credit note does not raise the group above the payable', function (): void {
    $invoice = createPayableOrder($this, $this->purchaseType, '-300.00');
    $creditNote = createPayableOrder($this, $this->refundType, '500.00', $invoice->contact_id);

    session(['payment_run_preview_orders' => [$invoice->getKey()]]);
    session(['payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer]);

    $component = Livewire::test(PaymentRunPreview::class);
    $groupKey = $component->get('groups')[0]['key'];

    $component->call('applyBalance', $groupKey, $creditNote->getKey());

    expect(data_get($component->get('groups'), '0.orders.1.amount'))->toBe('300.00')
        ->and(data_get($component->get('groups'), '0.amount'))->toBe('0.00');
});

test('removing the invoice row leaves a capped credit note from raising the total above zero', function (): void {
    $invoice = createPayableOrder($this, $this->purchaseType, '-300.00');
    createPayableOrder($this, $this->refundType, '500.00', $invoice->contact_id);

    session(['payment_run_preview_orders' => [$invoice->getKey()]]);
    session(['payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer]);

    $component = Livewire::test(PaymentRunPreview::class)
        ->call('removeOrder', $invoice->getKey());

    expect(bccomp(data_get($component->get('groups'), '0.amount'), '0', 2))->toBeLessThanOrEqual(0);
});

test('removing a suggested credit note raises the transfer again', function (): void {
    $invoice = createPayableOrder($this, $this->purchaseType, '-1000.00');
    $creditNote = createPayableOrder($this, $this->refundType, '200.00', $invoice->contact_id);

    session(['payment_run_preview_orders' => [$invoice->getKey()]]);
    session(['payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer]);

    $component = Livewire::test(PaymentRunPreview::class)
        ->call('removeOrder', $creditNote->getKey());

    expect(data_get($component->get('groups'), '0.amount'))->toBe('-1000.00');
});

test('creating the run sends one position per group', function (): void {
    $first = createPayableOrder($this, $this->purchaseType, '-1000.00');
    $second = createPayableOrder($this, $this->purchaseType, '-500.00', $first->contact_id);

    session(['payment_run_preview_orders' => [$first->getKey(), $second->getKey()]]);
    session(['payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer]);

    Livewire::test(PaymentRunPreview::class)->call('createPaymentRun');

    $run = PaymentRun::query()->latest('id')->first();

    expect($run->positions()->count())->toBe(1)
        ->and($run->positions()->first()->orders()->count())->toBe(2);
});

test('a group holding only credit notes is skipped instead of killing the whole run', function (): void {
    $invoice = createPayableOrder($this, $this->purchaseType, '-1000.00');
    $lonelyCreditNote = createPayableOrder($this, $this->refundType, '200.00');

    session(['payment_run_preview_orders' => [$invoice->getKey(), $lonelyCreditNote->getKey()]]);
    session(['payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer]);

    $component = Livewire::test(PaymentRunPreview::class)
        ->call('createPaymentRun')
        ->assertRedirect();

    $run = PaymentRun::query()->sole();

    expect(toastTypesOf($component))->toBe(['warning', 'success'])
        ->and($run->positions()->count())->toBe(1)
        ->and($run->positions()->first()->orders()->pluck('orders.id')->all())->toBe([$invoice->getKey()])
        ->and($lonelyCreditNote->fresh()->payment_state)->toBeInstanceOf(Open::class)
        ->and($invoice->fresh()->payment_state)->toBeInstanceOf(InOpenPaymentRun::class);
});

test('a run made of nothing but a lonely credit note is not created at all', function (): void {
    $lonelyCreditNote = createPayableOrder($this, $this->refundType, '200.00');

    session(['payment_run_preview_orders' => [$lonelyCreditNote->getKey()]]);
    session(['payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer]);

    $component = Livewire::test(PaymentRunPreview::class)
        ->call('createPaymentRun')
        ->assertNoRedirect();

    expect(toastTypesOf($component))->toBe(['warning'])
        ->and(PaymentRun::query()->count())->toBe(0)
        ->and($lonelyCreditNote->fresh()->payment_state)->toBeInstanceOf(Open::class);
});

test('a credit note already in another payment run is not suggested', function (): void {
    $invoice = createPayableOrder($this, $this->purchaseType, '-1000.00');
    $creditNote = createPayableOrder($this, $this->refundType, '200.00', $invoice->contact_id);

    $position = PaymentRunPosition::factory()->create();
    $position->orders()->attach($creditNote->getKey(), [
        'payment_run_id' => $position->payment_run_id,
        'amount' => '200.00',
    ]);

    session(['payment_run_preview_orders' => [$invoice->getKey()]]);
    session(['payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer]);

    $component = Livewire::test(PaymentRunPreview::class);

    expect(data_get($component->get('groups'), '0.amount'))->toBe('-1000.00')
        ->and(data_get($component->get('groups'), '0.orders'))->toHaveCount(1);
});

test('applying the balance amount updates the row and the group total', function (): void {
    session([
        'payment_run_preview_orders' => [$this->orders[0]->id],
        'payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer,
    ]);

    $component = Livewire::test(PaymentRunPreview::class);
    $groupKey = $component->get('groups')[0]['key'];

    $component->set('groups.0.orders.0.amount', 1)
        ->call('applyBalance', $groupKey, $this->orders[0]->id);

    expect(data_get($component->get('groups'), '0.orders.0.amount'))->toBe('-100.50')
        ->and(data_get($component->get('groups'), '0.amount'))->toBe('-100.50');
});

test('applying the discount amount updates the row and the group total', function (): void {
    DB::table('orders')->where('id', $this->orders[0]->id)->update([
        'balance_due_discount' => -95.48,
        'payment_discount_percent' => 0.05,
    ]);

    session([
        'payment_run_preview_orders' => [$this->orders[0]->id],
        'payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer,
    ]);

    $component = Livewire::test(PaymentRunPreview::class);
    $groupKey = $component->get('groups')[0]['key'];

    $component->call('applyDiscount', $groupKey, $this->orders[0]->id);

    expect(data_get($component->get('groups'), '0.orders.0.amount'))->toBe('-95.48')
        ->and(data_get($component->get('groups'), '0.amount'))->toBe('-95.48');
});

test('calculates total amount', function (): void {
    session([
        'payment_run_preview_orders' => [$this->orders[0]->id, $this->orders[1]->id],
        'payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer,
    ]);

    $component = Livewire::test(PaymentRunPreview::class);

    $rows = collect($component->get('groups'))->flatMap(fn (array $group) => $group['orders'])->keyBy('id');

    expect($rows[$this->orders[0]->id]['amount'])->toBe('-100.50');
    expect($rows[$this->orders[1]->id]['amount'])->toBe('-250.75');
    expect($rows[$this->orders[0]->id]['multiplier'])->toEqual(-1);
    expect($rows[$this->orders[1]->id]['multiplier'])->toEqual(-1);
});

test('can create payment run with multiplier', function (): void {
    session([
        'payment_run_preview_orders' => [$this->orders[0]->id],
        'payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer,
    ]);

    $component = Livewire::test(PaymentRunPreview::class);

    expect(data_get($component->get('groups'), '0.orders.0.multiplier'))->toEqual(-1);

    $component->call('createPaymentRun')
        ->assertRedirect();
});

test('can set valid amounts', function (): void {
    session([
        'payment_run_preview_orders' => [$this->orders[0]->id],
        'payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer,
    ]);

    $component = Livewire::test(PaymentRunPreview::class);

    $component->set('groups.0.orders.0.amount', 50.00);
    $component->assertSet('groups.0.orders.0.amount', 50.00);

    $component->set('groups.0.orders.0.amount', 75.25);
    $component->assertSet('groups.0.orders.0.amount', 75.25);
});

test('can update payment amounts', function (): void {
    session([
        'payment_run_preview_orders' => [$this->orders[0]->id],
        'payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer,
    ]);

    $component = Livewire::test(PaymentRunPreview::class);

    $component->set('groups.0.orders.0.amount', 80.00);

    $component->assertSet('groups.0.orders.0.amount', 80.00);
});

test('cancel redirects to money transfer', function (): void {
    session([
        'payment_run_preview_orders' => [$this->orders[0]->id],
        'payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer,
    ]);

    Livewire::test(PaymentRunPreview::class)
        ->call('cancel')
        ->assertRedirect(route('accounting.money-transfer'));
});

test('component initializes with order ids', function (): void {
    session([
        'payment_run_preview_orders' => [$this->orders[0]->id, $this->orders[1]->id],
        'payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer,
    ]);

    $component = Livewire::test(PaymentRunPreview::class);

    $rows = collect($component->get('groups'))->flatMap(fn (array $group) => $group['orders'])->keyBy('id');

    expect($rows)->toHaveCount(2);
    expect($rows[$this->orders[0]->id]['id'])->toEqual($this->orders[0]->id);
    expect($rows[$this->orders[1]->id]['id'])->toEqual($this->orders[1]->id);
    expect($rows[$this->orders[0]->id]['amount'])->toBe('-100.50');
    expect($rows[$this->orders[1]->id]['amount'])->toBe('-250.75');
    expect($rows[$this->orders[0]->id]['multiplier'])->toEqual(-1);
    expect($rows[$this->orders[1]->id]['multiplier'])->toEqual(-1);
});

test('creates payment run successfully', function (): void {
    session([
        'payment_run_preview_orders' => [$this->orders[0]->id, $this->orders[1]->id],
        'payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer,
    ]);

    expect(PaymentRun::count())->toEqual(0);

    Livewire::test(PaymentRunPreview::class)
        ->call('createPaymentRun')
        ->assertRedirect();

    expect(PaymentRun::count())->toBeGreaterThan(0);
});

test('creates payment run with custom amounts', function (): void {
    session([
        'payment_run_preview_orders' => [$this->orders[0]->id],
        'payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer,
    ]);

    Livewire::test(PaymentRunPreview::class)
        ->set('groups.0.orders.0.amount', 75.25)
        ->call('createPaymentRun')
        ->assertRedirect();

    expect(PaymentRun::count())->toBeGreaterThan(0);
});

test('displays orders in the preview', function (): void {
    session([
        'payment_run_preview_orders' => [$this->orders[0]->id, $this->orders[1]->id],
        'payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer,
    ]);

    $component = Livewire::test(PaymentRunPreview::class);

    $rows = collect($component->get('groups'))->flatMap(fn (array $group) => $group['orders'])->keyBy('id');

    expect($rows)->toHaveCount(2);
    expect($rows[$this->orders[0]->id]['invoice_number'])->toEqual('INV-001');
    expect($rows[$this->orders[1]->id]['invoice_number'])->toEqual('INV-002');

    $component->assertOk();
});

test('handles empty order ids', function (): void {
    session([
        'payment_run_preview_orders' => [],
        'payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer,
    ]);

    Livewire::test(PaymentRunPreview::class)
        ->assertRedirect(route('accounting.money-transfer'));
});

test('ignores non existent order ids', function (): void {
    session([
        'payment_run_preview_orders' => [$this->orders[0]->id, 999999],
        'payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer,
    ]);

    $component = Livewire::test(PaymentRunPreview::class);

    $rows = collect($component->get('groups'))->flatMap(fn (array $group) => $group['orders']);

    expect($rows)->toHaveCount(1);
});

test('preserves group and row data integrity', function (): void {
    session([
        'payment_run_preview_orders' => [$this->orders[0]->id],
        'payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer,
    ]);

    $component = Livewire::test(PaymentRunPreview::class);

    $group = $component->get('groups')[0];
    $row = $group['orders'][0];

    expect($row['id'])->toEqual($this->orders[0]->id);
    expect($row['invoice_number'])->toEqual('INV-001');
    expect($row['balance'])->toBe('-100.50');
    expect($group['contact_name'])->not->toBeEmpty();
});

test('redirects when missing payment run type', function (): void {
    session(['payment_run_preview_orders' => [1, 2]]);

    Livewire::test(PaymentRunPreview::class)
        ->assertRedirect(route('accounting.money-transfer'));
});

test('redirects when no session data', function (): void {
    Livewire::test(PaymentRunPreview::class)
        ->assertRedirect(route('accounting.money-transfer'));
});

test('renders successfully', function (): void {
    session([
        'payment_run_preview_orders' => [$this->orders[0]->id],
        'payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer,
    ]);

    Livewire::test(PaymentRunPreview::class)
        ->assertOk();
});

test('renders successfully with minimal session data', function (): void {
    session([
        'payment_run_preview_orders' => [9999, 9998],
        'payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer,
    ]);

    $component = Livewire::test(PaymentRunPreview::class);

    $component->assertOk();
    expect($component->get('groups'))->toBeEmpty();
});

test('shows notification on successful creation', function (): void {
    session([
        'payment_run_preview_orders' => [$this->orders[0]->id],
        'payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer,
    ]);

    Livewire::test(PaymentRunPreview::class)
        ->call('createPaymentRun')
        ->assertRedirect();
});

test('money transfer creates single payment run regardless of sepa mandate types', function (): void {
    $bankConnection1 = ContactBankConnection::factory()->create([
        'contact_id' => $this->contact->id,
    ]);

    SepaMandate::factory()->create([
        'contact_id' => $this->contact->id,
        'contact_bank_connection_id' => $bankConnection1->id,
        'sepa_mandate_type_enum' => SepaMandateTypeEnum::BASIC,
        'signed_date' => now(),
        'tenant_id' => $this->dbTenant->id,
    ]);

    $bankConnection2 = ContactBankConnection::factory()->create([
        'contact_id' => $this->contact->id,
    ]);

    SepaMandate::factory()->create([
        'contact_id' => $this->contact->id,
        'contact_bank_connection_id' => $bankConnection2->id,
        'sepa_mandate_type_enum' => SepaMandateTypeEnum::B2B,
        'signed_date' => now(),
        'tenant_id' => $this->dbTenant->id,
    ]);

    $this->orders[0]->update(['contact_bank_connection_id' => $bankConnection1->id]);
    $this->orders[1]->update(['contact_bank_connection_id' => $bankConnection2->id]);

    session([
        'payment_run_preview_orders' => [$this->orders[0]->id, $this->orders[1]->id],
        'payment_run_type_enum' => PaymentRunTypeEnum::MoneyTransfer,
    ]);

    Livewire::test(PaymentRunPreview::class)
        ->call('createPaymentRun')
        ->assertRedirect();

    expect(PaymentRun::count())->toEqual(1);
    expect(PaymentRun::first()->orders)->toHaveCount(2);
});

test('direct debit creates separate payment runs per sepa mandate type', function (): void {
    $directDebitPaymentType = PaymentType::factory()->create([
        'is_direct_debit' => true,
    ]);

    $bankConnection1 = ContactBankConnection::factory()->create([
        'contact_id' => $this->contact->id,
    ]);

    SepaMandate::factory()->create([
        'contact_id' => $this->contact->id,
        'contact_bank_connection_id' => $bankConnection1->id,
        'sepa_mandate_type_enum' => SepaMandateTypeEnum::BASIC,
        'signed_date' => now(),
        'tenant_id' => $this->dbTenant->id,
    ]);

    $bankConnection2 = ContactBankConnection::factory()->create([
        'contact_id' => $this->contact->id,
    ]);

    SepaMandate::factory()->create([
        'contact_id' => $this->contact->id,
        'contact_bank_connection_id' => $bankConnection2->id,
        'sepa_mandate_type_enum' => SepaMandateTypeEnum::B2B,
        'signed_date' => now(),
        'tenant_id' => $this->dbTenant->id,
    ]);

    $this->orders[0]->update([
        'contact_bank_connection_id' => $bankConnection1->id,
        'payment_type_id' => $directDebitPaymentType->id,
        'balance' => 100.50,
    ]);
    $this->orders[1]->update([
        'contact_bank_connection_id' => $bankConnection2->id,
        'payment_type_id' => $directDebitPaymentType->id,
        'balance' => 250.75,
    ]);

    session([
        'payment_run_preview_orders' => [$this->orders[0]->id, $this->orders[1]->id],
        'payment_run_type_enum' => PaymentRunTypeEnum::DirectDebit,
    ]);

    Livewire::test(PaymentRunPreview::class)
        ->call('createPaymentRun')
        ->assertRedirect();

    expect(PaymentRun::count())->toEqual(2);
});
