<?php

namespace FluxErp\Tests\Livewire\Widgets;

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
use FluxErp\States\Order\PaymentState\Open;
use FluxErp\States\Order\PaymentState\Paid;
use FluxErp\States\Order\PaymentState\PartialPaid;
use FluxErp\Tests\Livewire\BaseSetup;
use Illuminate\Support\Collection;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use Livewire\Livewire;

class OutstandingTest extends BaseSetup
{
    public array $orderTypeIds = [];

    protected string $livewireComponent = Outstanding::class;

    private Currency $currency;

    private string $inTimeDate;

    private string $overDueDate;

    protected function setUp(): void
    {
        parent::setUp();

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
    }

    public function test_calculate_sum_payment_state_all_time_relations_in_time(): void
    {
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

        $orders = $this->createData($paymentProps);

        Livewire::test($this->livewireComponent)
            ->call('calculateSum')
            ->assertSet('sum', $this->createSumString($orders))
            ->assertSet('subValue', $this->createSubString($orders))
            ->assertHasNoErrors()
            ->assertStatus(200);
    }

    public function test_calculate_sum_payment_state_all_time_relations_over_due(): void
    {
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

        $orders = $this->createData($paymentProps);

        Livewire::test($this->livewireComponent)
            ->call('calculateSum')
            ->assertSet('sum', $this->createSumString($orders))
            ->assertSet('subValue', $this->createSubString($orders))
            ->assertHasNoErrors()
            ->assertStatus(200);
    }

    public function test_calculate_sum_payment_state_open_time_relations_all(): void
    {
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

        $orders = $this->createData($paymentProps);

        Livewire::test($this->livewireComponent)
            ->call('calculateSum')
            ->assertSet('sum', $this->createSumString($orders))
            ->assertSet('subValue', $this->createSubString($orders))
            ->assertHasNoErrors()
            ->assertStatus(200);
    }

    public function test_calculate_sum_payment_state_paid_time_relations_all(): void
    {
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

        $orders = $this->createData($paymentProps);

        Livewire::test($this->livewireComponent)
            ->call('calculateSum')
            ->assertSet('sum', $this->createSumString($orders))
            ->assertSet('subValue', $this->createSubString($orders))
            ->assertHasNoErrors()
            ->assertStatus(200);
    }

    public function test_calculate_sum_payment_state_partial_paid_time_relations_all(): void
    {
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

        $orders = $this->createData($paymentProps);

        Livewire::test($this->livewireComponent)
            ->call('calculateSum')
            ->assertSet('sum', $this->createSumString($orders))
            ->assertSet('subValue', $this->createSubString($orders))
            ->assertHasNoErrors()
            ->assertStatus(200);
    }

    public function test_calculate_table_empty(): void
    {
        $orders = Collection::make([]);

        Livewire::test($this->livewireComponent)
            ->call('calculateSum')
            ->assertSet('sum', $this->createSumString($orders))
            ->assertSet('subValue', $this->createSubString($orders))
            ->assertHasNoErrors()
            ->assertStatus(200);
    }

    public function test_redirect_to_orders(): void
    {
        Livewire::test($this->livewireComponent)
            ->call('show')
            ->assertRedirect(route('orders.orders'))
            ->assertHasNoErrors()
            ->assertStatus(200);
    }

    public function test_redirect_to_over_due(): void
    {
        Livewire::test($this->livewireComponent)
            ->call('showOverdue')
            ->assertRedirect(route('accounting.payment-reminders'))
            ->assertHasNoErrors()
            ->assertStatus(200);
    }

    public function test_renders_successfully(): void
    {
        $this->createData(collect());

        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }

    private function createData(Collection $paymentProps): Collection
    {
        $orders = collect();

        $contact = Contact::factory()->create([
            'client_id' => $this->dbClient->getKey(),
        ]);

        $address = Address::factory()->create([
            'client_id' => $this->dbClient->getKey(),
            'contact_id' => $contact->id,
        ]);

        $priceList = PriceList::factory()->create();

        $language = Language::factory()->create();

        $orderType = OrderType::factory()->create([
            'client_id' => $this->dbClient->getKey(),
            'order_type_enum' => OrderTypeEnum::Order,
        ]);

        $paymentType = PaymentType::factory()
            ->hasAttached(factory: $this->dbClient, relationship: 'clients')
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
                        'client_id' => $this->dbClient->getKey(),
                        'language_id' => $language->id,
                        'invoice_date' => $invoice->id,
                        'invoice_number' => $invoice->invoice_number,
                        'order_type_id' => $orderType->id,
                        'payment_type_id' => $paymentType->id,
                        'price_list_id' => $priceList->id,
                        'currency_id' => $this->currency->id,
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

    private function createSubString(Collection $orders): string
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
                    $this->orderTypeIds
                )
                ->sum('balance'),
            2)
        . ' ' . Currency::default()->symbol . ' ' . __('Overdue')
        . '</span>';
    }

    private function createSumString(Collection $orders): string
    {
        return Number::abbreviate(
            $orders
                ->whereNotNull('invoice_date')
                ->whereNotNull('invoice_number')
                ->where('payment_state', '!=', Paid::$name)
                ->sum('balance'),
            2
        ) . ' ' . Currency::default()->symbol;
    }
}
