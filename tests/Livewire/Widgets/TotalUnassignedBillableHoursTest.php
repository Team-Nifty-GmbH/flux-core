<?php

namespace FluxErp\Tests\Livewire\Widgets;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Livewire\Widgets\TotalUnassignedBillableHours;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Language;
use FluxErp\Models\Order;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\WorkTime;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class TotalUnassignedBillableHoursTest extends BaseSetup
{
    protected string $livewireComponent = TotalUnassignedBillableHours::class;

    private WorkTime $workTime;

    protected function setUp(): void
    {
        parent::setUp();

        $contact = Contact::factory()->create([
            'client_id' => $this->dbClient->getKey(),
        ]);

        $address = Address::factory()->create([
            'client_id' => $this->dbClient->getKey(),
            'contact_id' => $contact->id,
        ]);

        $priceList = PriceList::factory()->create();

        $currency = Currency::factory()->create([
            'is_default' => true,
        ]);

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

        $order_position = $order = Order::factory()->create([
            'client_id' => $this->dbClient->getKey(),
            'language_id' => $language->id,
            'order_type_id' => $orderType->id,
            'payment_type_id' => $paymentType->id,
            'price_list_id' => $priceList->id,
            'currency_id' => $currency->id,
            'address_invoice_id' => $address->id,
            'address_delivery_id' => $address->id,
            'is_locked' => false,
        ]);

        OrderPosition::factory()->create([
            'client_id' => $this->dbClient->getKey(),
            'order_id' => $order->id,
        ]);

        $this->workTime = WorkTime::factory()
            ->for($this->user)
            ->create([
                'order_position_id' => null,
                'is_daily_work_time' => false,
                'is_billable' => true,
                'started_at' => Carbon::now()->subHours(2)->toDateTimeString(),
                'ended_at' => Carbon::now()->toDateTimeString(),
                'total_time_ms' => 7200000,
            ]);

        WorkTime::factory()
            ->for($this->user)
            ->create([
                'order_position_id' => null,
                'is_daily_work_time' => true,
                'is_billable' => true,
                'started_at' => Carbon::now()->subHours(2)->toDateTimeString(),
                'ended_at' => Carbon::now()->toDateTimeString(),
                'total_time_ms' => 7200000,
            ]);

        WorkTime::factory()
            ->for($this->user)
            ->create([
                'order_position_id' => null,
                'is_daily_work_time' => false,
                'is_billable' => false,
                'started_at' => Carbon::now()->subHours(2)->toDateTimeString(),
                'ended_at' => Carbon::now()->toDateTimeString(),
                'total_time_ms' => 7200000,
            ]);

        WorkTime::factory()
            ->for($this->user)
            ->create([
                'order_position_id' => $order_position->id,
                'is_daily_work_time' => false,
                'is_billable' => false,
                'started_at' => Carbon::now()->subHours(2)->toDateTimeString(),
                'ended_at' => Carbon::now()->toDateTimeString(),
                'total_time_ms' => 7200000,
            ]);
    }

    public function calculateDisplayedTime(int $ms): string
    {
        return CarbonInterval::milliseconds($ms)->cascade()->forHumans([
            'parts' => 2,
            'join' => true,
            'short' => true,
            'locale' => 'en',
        ]);
    }

    public function test_calculates_correct_sum_of_unassigned_billable_hours(): void
    {
        Livewire::test($this->livewireComponent)
            ->assertSet('sum', $this->calculateDisplayedTime($this->workTime->total_time_ms))
            ->assertHasNoErrors()
            ->assertStatus(200);
    }

    public function test_renders_successfully(): void
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }
}
