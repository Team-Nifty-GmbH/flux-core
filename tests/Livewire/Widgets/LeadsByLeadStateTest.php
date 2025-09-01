<?php

uses(FluxErp\Tests\Livewire\BaseSetup::class);
use Carbon\Carbon;
use FluxErp\Enums\TimeFrameEnum;
use FluxErp\Livewire\Widgets\LeadsByLeadState;
use FluxErp\Models\Lead;
use FluxErp\Models\LeadState;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->leadStates = collect([
        LeadState::factory()->create([
            'name' => 'New',
            'color' => '#2E93fA',
        ]),
        LeadState::factory()->create([
            'name' => 'In Progress',
            'color' => '#66DA26',
        ]),
        LeadState::factory()->create([
            'name' => 'Won',
            'color' => '#546E7A',
            'is_won' => true,
        ]),
    ]);

    $this->leads = collect();

    foreach ($this->leadStates as $leadState) {
        $quantity = $leadState->id === data_get($this->leadStates, '1.id') ? 2 : 1;

        $this->leads = $this->leads
            ->merge(
                Lead::factory()->count($quantity)->create([
                    'lead_state_id' => $leadState->id,
                    'created_at' => Carbon::now(),
                ])
            )
            ->merge(
                Lead::factory()->count($quantity)->create([
                    'lead_state_id' => $leadState->id,
                    'created_at' => Carbon::now()->startOfWeek(),
                ])
            )
            ->merge(
                Lead::factory()->count($quantity)->create([
                    'lead_state_id' => $leadState->id,
                    'created_at' => Carbon::now()->startOfMonth(),
                ])
            )
            ->merge(
                Lead::factory()->count($quantity)->create([
                    'lead_state_id' => $leadState->id,
                    'created_at' => Carbon::now()->startOfQuarter(),
                ])
            )
            ->merge(
                Lead::factory()->count($quantity)->create([
                    'lead_state_id' => $leadState->id,
                    'created_at' => Carbon::now()->startOfYear(),
                ])
            );
    }
});

test('options return expected structure', function (): void {
    $test = Livewire::test(LeadsByLeadState::class)
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
        expect(data_get($option, 'params'))->toHaveKey('name');
    }
});

test('options use data correctly', function (): void {
    $exampleData = [
        [
            'id' => data_get($this->leadStates, '0.id'),
            'name' => 'Test Lead State',
            'color' => '#123456',
            'total' => 5,
        ],
    ];

    $test = Livewire::test(LeadsByLeadState::class);

    $instance = $test->instance();
    $reflection = new ReflectionProperty($instance, 'data');
    $reflection->setValue($instance, $exampleData);

    $options = $instance->options();

    expect($options)->toBeArray();
    expect($options)->toHaveCount(count($exampleData));
    expect(data_get($options, '0.params.id'))->toEqual(data_get($this->leadStates, '0.id'));
    expect(data_get($options, '0.params.name'))->toEqual(data_get($exampleData, '0.name'));
});

test('renders successfully', function (): void {
    Livewire::test(LeadsByLeadState::class)
        ->assertStatus(200);
});

test('show method redirects correctly', function (): void {
    $params = [
        'id' => 1,
        'name' => 'New',
    ];

    Livewire::test(LeadsByLeadState::class)
        ->call('show', $params)
        ->assertRedirect(route('sales.leads'));
});

test('timeframe in the future', function (): void {
    $start = Carbon::now()->addDay();
    $end = Carbon::now()->addDays(2);
    $timeFrame = TimeFrameEnum::Custom;

    Livewire::test(LeadsByLeadState::class)
        ->set('timeFrame', $timeFrame)
        ->set('start', $start)
        ->set('end', $end)
        ->call('calculateChart')
        ->assertSet('series', [])
        ->assertStatus(200)
        ->assertHasNoErrors();
});

test('timeframe this month', function (): void {
    assertLeadStateTimeframeResults(TimeFrameEnum::ThisMonth);
});

test('timeframe this quarter', function (): void {
    assertLeadStateTimeframeResults(TimeFrameEnum::ThisQuarter);
});

test('timeframe this week', function (): void {
    assertLeadStateTimeframeResults(TimeFrameEnum::ThisWeek);
});

test('timeframe this year', function (): void {
    assertLeadStateTimeframeResults(TimeFrameEnum::ThisYear);
});

test('timeframe today', function (): void {
    assertLeadStateTimeframeResults(TimeFrameEnum::Today);
});

function assertLeadStateTimeframeResults(TimeFrameEnum $timeFrame): void
{
    $test = Livewire::test(LeadsByLeadState::class)
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
    expect($series)->not->toBeEmpty();
    expect($labels)->not->toBeEmpty();
    expect($data)->not->toBeEmpty();

    foreach ($this->leadStates as $leadState) {
        $expectedCount = getLeadStateLeadsCountInTimeFrame($timeFrame, $leadState);
        $index = array_search($leadState->name, $labels);

        if ($index !== false && $expectedCount > 0) {
            expect(data_get($series, $index))->toEqual($expectedCount);
        }
    }

    // Verify counts match expected values
    foreach ($this->leadStates as $leadState) {
        $expectedCount = getLeadStateLeadsCountInTimeFrame($timeFrame, $leadState);
        $index = array_search($leadState->name, $labels);

        if ($index !== false && $expectedCount > 0) {
            expect(data_get($series, $index))->toEqual($expectedCount);
        }
    }
}

function getLeadStateLeadsCountInTimeFrame(TimeFrameEnum $timeFrame, LeadState $leadState): int
{
    return $this->leads
        ->filter(
            fn (Lead $lead) => $lead->lead_state_id === $leadState->id
                && $lead->created_at->between(...$timeFrame->getRange())
        )
        ->count();
}
