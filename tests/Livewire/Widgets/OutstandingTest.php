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
    protected string $livewireComponent = Outstanding::class;

    private Currency $currency;

    private string $symbol;

    private string $overDueDate;

    private string $inTimeDate;

    protected function setUp(): void
    {
        parent::setUp();

        $this->currency = Currency::factory()->create([
            'is_default' => true,
        ]);

        $this->symbol = Currency::default()->symbol;

        $this->overDueDate = Carbon::now()->subDay()->toDateString();

        $this->inTimeDate = Carbon::now()->addDay()->toDateString();
    }

    public function test_renders_successfully(): void
    {
        $this->createData(collect());

        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }

    public function test_calculate_sum_payment_state_all_time_relations_in_time()
    {
        $paymentProps = collect([
            [
                Paid::class, $this->inTimeDate,
            ],
            [
                PartialPaid::class, $this->inTimeDate,
            ],
            [
                Open::class, $this->inTimeDate,
            ],
        ]);

        $orders = $this->createData($paymentProps);

        $sum = $this->createSumString($orders);

        $subValue = $this->createSubString($orders);

        Livewire::test($this->livewireComponent)
            ->call('calculateSum')
            ->assertSet('sum', $sum)
            ->assertSet('subValue', $subValue)
            ->assertHasNoErrors()
            ->assertStatus(200);
    }

    public function test_calculate_sum_payment_state_all_time_relations_over_due()
    {
        $paymentProps = collect([
            [
                Paid::class, $this->overDueDate,
            ],
            [
                PartialPaid::class, $this->overDueDate,
            ],
            [
                Open::class, $this->overDueDate,
            ],
        ]);

        $orders = $this->createData($paymentProps);

        $sum = $this->createSumString($orders);

        $subValue = $this->createSubString($orders);

        Livewire::test($this->livewireComponent)
            ->call('calculateSum')
            ->assertSet('sum', $sum)
            ->assertSet('subValue', $subValue)
            ->assertHasNoErrors()
            ->assertStatus(200);
    }

    public function test_calculate_sum_payment_state_paid_time_relations_all()
    {
        $paymentProps = collect([
            [
                Paid::class, $this->overDueDate,
            ],
            [
                Paid::class, $this->inTimeDate,
            ],
        ]);

        $orders = $this->createData($paymentProps);

        $sum = $this->createSumString($orders);

        $subValue = $this->createSubString($orders);

        Livewire::test($this->livewireComponent)
            ->call('calculateSum')
            ->assertSet('sum', $sum)
            ->assertSet('subValue', $subValue)
            ->assertHasNoErrors()
            ->assertStatus(200);
    }

    public function test_calculate_sum_payment_state_partial_paid_time_relations_all()
    {
        $paymentProps = collect([
            [
                PartialPaid::class, $this->overDueDate,
            ],
            [
                PartialPaid::class, $this->inTimeDate,
            ],
        ]);

        $orders = $this->createData($paymentProps);

        $sum = $this->createSumString($orders);

        $subValue = $this->createSubString($orders);

        Livewire::test($this->livewireComponent)
            ->call('calculateSum')
            ->assertSet('sum', $sum)
            ->assertSet('subValue', $subValue)
            ->assertHasNoErrors()
            ->assertStatus(200);
    }

    public function test_calculate_sum_payment_state_open_time_relations_all()
    {
        $paymentProps = collect([
            [
                Open::class, $this->overDueDate,
            ],
            [
                Open::class, $this->inTimeDate,
            ],
        ]);

        $orders = $this->createData($paymentProps);

        $sum = $this->createSumString($orders);

        $subValue = $this->createSubString($orders);

        Livewire::test($this->livewireComponent)
            ->call('calculateSum')
            ->assertSet('sum', $sum)
            ->assertSet('subValue', $subValue)
            ->assertHasNoErrors()
            ->assertStatus(200);
    }

    public function test_calculate_table_empty()
    {
        $orders = Order::all();

        $sum = $this->createSumString($orders);

        $subValue = $this->createSubString($orders);

        Livewire::test($this->livewireComponent)
            ->call('calculateSum')
            ->assertSet('sum', $sum)
            ->assertSet('subValue', $subValue)
            ->assertHasNoErrors()
            ->assertStatus(200);
    }

    public function test_redirect_to_orders()
    {
        Livewire::test($this->livewireComponent)
            ->call('show')
            ->assertRedirect(route('orders.orders'))
            ->assertHasNoErrors()
            ->assertStatus(200);
    }

    public function test_redirect_to_over_due()
    {
        Livewire::test($this->livewireComponent)
            ->call('showOverdue')
            ->assertRedirect(route('accounting.payment-reminders'))
            ->assertHasNoErrors()
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
                        'payment_state' => $paymentProp[0],
                        'payment_reminder_next_date' => $paymentProp[1],

                    ])
            );
        }

        return $orders;
    }

    private function createSubString(Collection $orders): string
    {
        return '<span class="text-negative-600">'
        . Number::abbreviate(
            $orders
                ->where('payment_state', '!=', 'paid')
                ->where('payment_reminder_next_date', '<=', now()->toDate())
                ->sum('balance'),
            2)
        . ' ' . $this->symbol . ' ' . __('Overdue')
        . '</span>';
    }

    private function createSumString(Collection $orders): string
    {
        return Number::abbreviate(
            $orders
                ->where('payment_state', '!=', 'paid')
                ->sum('balance'),
            2
        ) . ' ' . $this->symbol;
    }
}
