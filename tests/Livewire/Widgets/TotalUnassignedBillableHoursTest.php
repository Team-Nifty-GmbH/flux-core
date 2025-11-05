<?php

use Carbon\Carbon;
use Carbon\CarbonInterval;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Livewire\DataTables\WorkTimeList;
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
use Livewire\Livewire;

beforeEach(function (): void {
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

    $order = Order::factory()->create([
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

    $orderPosition = OrderPosition::factory()->create([
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
            'order_position_id' => $orderPosition->id,
            'is_daily_work_time' => false,
            'is_billable' => false,
            'started_at' => Carbon::now()->subHours(2)->toDateTimeString(),
            'ended_at' => Carbon::now()->toDateTimeString(),
            'total_time_ms' => 7200000,
        ]);
});

test('calculates correct sum of unassigned billable hours', function (): void {
    Livewire::test(TotalUnassignedBillableHours::class)
        ->assertSet('sum', calculateDisplayedTime($this->workTime->total_time_ms))
        ->assertHasNoErrors()
        ->assertOk();
});

test('renders successfully', function (): void {
    Livewire::test(TotalUnassignedBillableHours::class)
        ->assertOk();
});

test('show method redirects to work times route', function (): void {
    Livewire::test(TotalUnassignedBillableHours::class)
        ->call('show')
        ->assertRedirect(route('human-resources.work-times'));
});

test('show method creates session filter', function (): void {
    $component = Livewire::test(TotalUnassignedBillableHours::class);

    $component->call('show');

    $workTimeListCacheKey = Livewire::new(WorkTimeList::class)->getCacheKey();

    expect(session()->has('session-filters.' . $workTimeListCacheKey))->toBeTrue();
});

test('show method filters correct work times', function (): void {
    $component = Livewire::test(TotalUnassignedBillableHours::class);

    $component->call('show');

    $workTimeListCacheKey = Livewire::new(WorkTimeList::class)->getCacheKey();
    $sessionFilter = session('session-filters.' . $workTimeListCacheKey);

    expect($sessionFilter)->not->toBeNull()
        ->and($sessionFilter['label'])->toBe(__(TotalUnassignedBillableHours::getLabel()));

    $filteredWorkTimes = WorkTime::query()
        ->tap($sessionFilter['callback'])
        ->get();

    expect($filteredWorkTimes)->toHaveCount(1)
        ->and($filteredWorkTimes->first()->getKey())->toBe($this->workTime->getKey());
});

function calculateDisplayedTime(int $ms): string
{
    $interval = CarbonInterval::milliseconds($ms)->cascade();

    $totalHours = (int) $interval->totalHours;
    $minutes = $interval->minutes;

    return __('time.hours_minutes', [
        'hours' => $totalHours,
        'minutes' => $minutes,
    ]);
}
