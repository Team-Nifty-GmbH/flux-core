<?php

uses(FluxErp\Tests\Livewire\BaseSetup::class);
use Carbon\Carbon;
use FluxErp\Enums\TimeFrameEnum;
use FluxErp\Livewire\Widgets\LeadsByReferralSource;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Lead;
use FluxErp\Models\PriceList;
use Livewire\Livewire;

beforeEach(function (): void {
    $priceList = PriceList::factory()->create();

    $contacts = Contact::factory()->count(2)->create([
        'price_list_id' => $priceList->id,
        'client_id' => $this->dbClient->getKey(),
    ]);

    $this->addresses = collect([
        Address::factory()->create([
            'name' => 'Website',
            'client_id' => $this->dbClient->getKey(),
            'contact_id' => $contacts->random()->id,
        ]),
        Address::factory()->create([
            'name' => 'Social Media',
            'client_id' => $this->dbClient->getKey(),
            'contact_id' => $contacts->random()->id,
        ]),
        Address::factory()->create([
            'name' => 'Partner Referral',
            'client_id' => $this->dbClient->getKey(),
            'contact_id' => $contacts->random()->id,
        ]),
    ]);

    $this->leads = collect();

    foreach ($this->addresses as $address) {
        $quantity = $address->id === data_get($this->addresses, '1.id') ? 2 : 1;

        $this->leads = $this->leads
            ->merge(
                Lead::factory()->count($quantity)->create([
                    'recommended_by_address_id' => $address->id,
                    'created_at' => Carbon::now(),
                ])
            )
            ->merge(
                Lead::factory()->count($quantity)->create([
                    'recommended_by_address_id' => $address->id,
                    'created_at' => Carbon::now()->startOfWeek(),
                ])
            )
            ->merge(
                Lead::factory()->count($quantity)->create([
                    'recommended_by_address_id' => $address->id,
                    'created_at' => Carbon::now()->startOfMonth(),
                ])
            )
            ->merge(
                Lead::factory()->count($quantity)->create([
                    'recommended_by_address_id' => $address->id,
                    'created_at' => Carbon::now()->startOfQuarter(),
                ])
            )
            ->merge(
                Lead::factory()->count($quantity)->create([
                    'recommended_by_address_id' => $address->id,
                    'created_at' => Carbon::now()->startOfYear(),
                ])
            );
    }
});

test('options return expected structure', function (): void {
    $test = Livewire::test(LeadsByReferralSource::class)
        ->set('timeFrame', TimeFrameEnum::ThisMonth)
        ->call('calculateChart');

    $options = $test->instance()->options();

    expect($options)->toBeArray();
    expect($options)->not->toBeEmpty();

    foreach ($options as $option) {
        expect($option)->toHaveKey('label');
        expect($option)->toHaveKey('method');
        expect(data_get($option, 'method'))->toEqual('show');
        expect($option)->toHaveKey('params');
        expect(data_get($option, 'params'))->toHaveKey('id');
        expect(data_get($option, 'params'))->toHaveKey('label');
    }
});

test('options use data correctly', function (): void {
    $exampleData = [
        [
            'id' => data_get($this->addresses, '0.id'),
            'label' => 'Test Referral Source',
            'total' => 5,
        ],
    ];

    $test = Livewire::test(LeadsByReferralSource::class);

    $instance = $test->instance();
    $reflection = new ReflectionProperty($instance, 'data');
    $reflection->setValue($instance, $exampleData);

    $options = $instance->options();

    expect($options)->toBeArray();
    expect($options)->toHaveCount(count($exampleData));
    expect(data_get($options, '0.params.id'))->toEqual(data_get($this->addresses, '0.id'));
    expect(data_get($options, '0.params.label'))->toEqual(data_get($exampleData, '0.label'));
});

test('renders successfully', function (): void {
    Livewire::test(LeadsByReferralSource::class)
        ->assertStatus(200);
});

test('show method redirects correctly', function (): void {
    $params = [
        'id' => 1,
        'label' => 'Website',
    ];

    Livewire::test(LeadsByReferralSource::class)
        ->call('show', $params)
        ->assertRedirect(route('sales.leads'));
});

test('timeframe in the future', function (): void {
    $start = Carbon::now()->addDay();
    $end = Carbon::now()->addDays(2);
    $timeFrame = TimeFrameEnum::Custom;

    Livewire::test(LeadsByReferralSource::class)
        ->set('timeFrame', $timeFrame)
        ->set('start', $start)
        ->set('end', $end)
        ->call('calculateChart')
        ->assertSet('series', [])
        ->assertStatus(200)
        ->assertHasNoErrors();
});

test('timeframe this month', function (): void {
    assertReferralSourceTimeframeResults(TimeFrameEnum::ThisMonth);
});

test('timeframe this quarter', function (): void {
    assertReferralSourceTimeframeResults(TimeFrameEnum::ThisQuarter);
});

test('timeframe this week', function (): void {
    assertReferralSourceTimeframeResults(TimeFrameEnum::ThisWeek);
});

test('timeframe this year', function (): void {
    assertReferralSourceTimeframeResults(TimeFrameEnum::ThisYear);
});

test('timeframe today', function (): void {
    assertReferralSourceTimeframeResults(TimeFrameEnum::Today);
});

function assertReferralSourceTimeframeResults(TimeFrameEnum $timeFrame): void
{
    $test = Livewire::test(LeadsByReferralSource::class)
        ->set('timeFrame', $timeFrame)
        ->call('calculateChart')
        ->assertStatus(200)
        ->assertHasNoErrors();

    $series = $test->get('series');
    $labels = $test->get('labels');
    $data = $test->get('data');

    expect($series)->toBeArray();
    expect($labels)->toBeArray();
    expect($data)->toBeArray();

    foreach ($this->addresses as $address) {
        $expectedCount = getReferralSourceLeadsCountInTimeFrame($timeFrame, $address);

        if ($expectedCount > 0) {
            $addressLabel = $address->getLabel();
            $index = array_search($addressLabel, $labels);

            if ($index !== false) {
                expect(data_get($series, $index))->toEqual($expectedCount);
            }
        }
    }
}

function getReferralSourceLeadsCountInTimeFrame(TimeFrameEnum $timeFrame, Address $address): int
{
    return $this->leads
        ->filter(
            fn (Lead $lead) => $lead->recommended_by_address_id === $address->id
                && $lead->created_at->between(...$timeFrame->getRange())
        )
        ->count();
}
