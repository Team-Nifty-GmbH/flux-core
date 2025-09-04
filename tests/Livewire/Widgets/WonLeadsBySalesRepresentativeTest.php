<?php

use Carbon\Carbon;
use FluxErp\Enums\TimeFrameEnum;
use FluxErp\Livewire\Widgets\WonLeadsBySalesRepresentative;
use FluxErp\Models\Language;
use FluxErp\Models\Lead;
use FluxErp\Models\LeadState;
use FluxErp\Models\User;
use Illuminate\Support\Collection;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->wonLeadState = LeadState::factory()->create([
        'name' => 'Won',
        'is_won' => true,
    ]);

    $language = Language::factory()->create();

    $this->users = collect([
        User::factory()->create([
            'name' => 'Sales Rep 1',
            'language_id' => $language->id,
        ]),
        User::factory()->create([
            'name' => 'Sales Rep 2',
            'language_id' => $language->id,
        ]),
        User::factory()->create([
            'name' => 'Sales Rep 3',
            'language_id' => $language->id,
        ]),
    ]);

    $this->leads = collect();

    foreach ($this->users as $user) {
        $quantity = $user->id === data_get($this->users, '1.id') ? 2 : 1;

        $this->leads = $this->leads
            ->merge(
                Lead::factory()->count($quantity)->create([
                    'user_id' => $user->id,
                    'lead_state_id' => $this->wonLeadState->id,
                    'end' => Carbon::now(),
                ])
            )
            ->merge(
                Lead::factory()->count($quantity)->create([
                    'user_id' => $user->id,
                    'lead_state_id' => $this->wonLeadState->id,
                    'end' => Carbon::now()->startOfWeek(),
                ])
            )
            ->merge(
                Lead::factory()->count($quantity)->create([
                    'user_id' => $user->id,
                    'lead_state_id' => $this->wonLeadState->id,
                    'end' => Carbon::now()->startOfMonth(),
                ])
            )
            ->merge(
                Lead::factory()->count($quantity)->create([
                    'user_id' => $user->id,
                    'lead_state_id' => $this->wonLeadState->id,
                    'end' => Carbon::now()->startOfQuarter(),
                ])
            )
            ->merge(
                Lead::factory()->count($quantity)->create([
                    'user_id' => $user->id,
                    'lead_state_id' => $this->wonLeadState->id,
                    'end' => Carbon::now()->startOfYear(),
                ])
            );
    }
});

test('options return empty array when series is null', function (): void {
    $test = Livewire::test(WonLeadsBySalesRepresentative::class);

    $instance = $test->instance();
    $ref = new ReflectionProperty($instance, 'series');
    $ref->setValue($instance, null);

    $test->tap(function ($test): void {
        $result = $test->instance()->options();
        expect($result)->toBeArray();
        expect($result)->toBeEmpty();
    });
});

test('options return expected structure', function (): void {
    $test = Livewire::test(WonLeadsBySalesRepresentative::class)
        ->set('timeFrame', TimeFrameEnum::ThisMonth)
        ->call('calculateChart');

    $options = $test->instance()->options();

    expect($options)->toBeArray();
    expect($options)->not->toBeEmpty();

    $expectedLabels = $this->users->pluck('name')->toArray();

    foreach ($options as $option) {
        expect($option)->toHaveKey('label');
        expect($option)->toHaveKey('method');
        expect(data_get($option, 'method'))->toEqual('show');
        expect($option)->toHaveKey('params');
        expect(data_get($option, 'params'))->toHaveKey('id');
        expect(data_get($option, 'params'))->toHaveKey('name');

        expect($expectedLabels)->toContain(data_get($option, 'label'));
    }
});

test('options use data correctly', function (): void {
    $exampleSeries = [
        [
            'id' => data_get($this->users, '0.id'),
            'name' => 'Test Sales Rep',
            'color' => '#123456',
            'data' => [5],
        ],
    ];

    $test = Livewire::test(WonLeadsBySalesRepresentative::class);

    $instance = $test->instance();
    $reflection = new ReflectionProperty($instance, 'series');
    $reflection->setValue($instance, $exampleSeries);

    $options = $instance->options();

    expect($options)->toBeArray();
    expect($options)->toHaveCount(1);
    expect(data_get($options, '0.label'))->toEqual(data_get($exampleSeries, '0.name'));
    expect(data_get($options, '0.params.id'))->toEqual(data_get($this->users, '0.id'));
    expect(data_get($options, '0.params.name'))->toEqual(data_get($exampleSeries, '0.name'));
});

test('renders successfully', function (): void {
    Livewire::test(WonLeadsBySalesRepresentative::class)
        ->assertStatus(200);
});

test('show method redirects correctly', function (): void {
    $params = [
        'id' => 1,
        'name' => 'Sales Rep 1',
    ];

    Livewire::test(WonLeadsBySalesRepresentative::class)
        ->call('show', $params)
        ->assertRedirect(route('sales.leads'));
});

test('timeframe in the future', function (): void {
    $start = Carbon::now()->addDay();
    $end = Carbon::now()->addDays(2);
    $timeFrame = TimeFrameEnum::Custom;

    Livewire::test(WonLeadsBySalesRepresentative::class)
        ->set('timeFrame', $timeFrame)
        ->set('start', $start)
        ->set('end', $end)
        ->call('calculateChart')
        ->assertSet('series', [])
        ->assertStatus(200)
        ->assertHasNoErrors();
});

test('timeframe this month', function (): void {
    assertTimeframeResults($this->users, $this->leads, TimeFrameEnum::ThisMonth);
});

test('timeframe this quarter', function (): void {
    assertTimeframeResults($this->users, $this->leads, TimeFrameEnum::ThisQuarter);
});

test('timeframe this week', function (): void {
    assertTimeframeResults($this->users, $this->leads, TimeFrameEnum::ThisWeek);
});

test('timeframe this year', function (): void {
    assertTimeframeResults($this->users, $this->leads, TimeFrameEnum::ThisYear);
});

test('timeframe today', function (): void {
    assertTimeframeResults($this->users, $this->leads, TimeFrameEnum::Today);
});

function assertTimeframeResults(Collection $users, Collection $leads, TimeFrameEnum $timeFrame): void
{
    $test = Livewire::test(WonLeadsBySalesRepresentative::class)
        ->set('timeFrame', $timeFrame)
        ->call('calculateChart')
        ->assertStatus(200)
        ->assertHasNoErrors();

    $series = $test->get('series');
    expect($series)->toBeArray();
    expect($series)->not->toBeEmpty();

    $expected = $users->map(function ($user) use ($timeFrame, $leads) {
        return [
            'name' => $user->name,
            'count' => getWonLeadCountInTimeFrame($leads, $timeFrame, $user),
        ];
    })
        ->sortByDesc('count')
        ->values();

    foreach ($expected as $index => $userData) {
        expect(data_get($series, "{$index}.name"))->toEqual($userData['name']);
        expect(data_get($series, "{$index}.data.0"))->toEqual($userData['count']);
    }
}

function getWonLeadCountInTimeFrame(Collection $leads, TimeFrameEnum $timeFrame, User $user): int
{
    return $leads
        ->filter(
            fn (Lead $lead) => $lead->user_id === $user->id
                && $lead->leadState->is_won === true
                && $lead->end->between(...$timeFrame->getRange())
        )
        ->count();
}
