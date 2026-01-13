<?php

use Carbon\Carbon;
use FluxErp\Enums\TimeFrameEnum;
use FluxErp\Livewire\Widgets\LeadsByRecordOrigin;
use FluxErp\Models\Lead;
use FluxErp\Models\RecordOrigin;
use Illuminate\Support\Collection;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->recordOrigins = collect([
        RecordOrigin::factory()->create([
            'name' => 'testOrigin1',
            'is_active' => true,
            'model_type' => morph_alias(Lead::class),
        ]),
        RecordOrigin::factory()->create([
            'name' => 'testOrigin2',
            'is_active' => true,
            'model_type' => morph_alias(Lead::class),
        ]),
        RecordOrigin::factory()->create([
            'name' => 'testOrigin3',
            'is_active' => false,
            'model_type' => morph_alias(Lead::class),
        ]),
    ]);

    $this->leads = collect();

    foreach ($this->recordOrigins as $recordOrigin) {
        $quantity = $recordOrigin->getKey() === $this->recordOrigins[1]->getKey() ? 2 : 1;

        $this->leads = $this->leads
            ->merge(
                Lead::factory()->count($quantity)->create([
                    'record_origin_id' => $recordOrigin->getKey(),
                ])
            )
            ->merge(
                Lead::factory()->count($quantity)->create([
                    'record_origin_id' => $recordOrigin->getKey(),
                    'created_at' => Carbon::now()->startOfWeek(),
                ])
            )
            ->merge(
                Lead::factory()->count($quantity)->create([
                    'record_origin_id' => $recordOrigin->getKey(),
                    'created_at' => Carbon::now()->startOfMonth(),
                ])
            )
            ->merge(
                Lead::factory()->count($quantity)->create([
                    'record_origin_id' => $recordOrigin->getKey(),
                    'created_at' => Carbon::now()->startOfQuarter(),
                ])
            )
            ->merge(
                Lead::factory()->count($quantity)->create([
                    'record_origin_id' => $recordOrigin->getKey(),
                    'created_at' => Carbon::now()->startOfYear(),
                ])
            );
    }
});

test('renders successfully', function (): void {
    Livewire::test(LeadsByRecordOrigin::class)
        ->assertOk();
});

test('timeframe in the future', function (): void {
    $start = Carbon::now()->addDay()->toDateString();
    $end = Carbon::now()->addDays(2)->toDateString();
    $timeFrame = TimeFrameEnum::Custom;

    Livewire::test(LeadsByRecordOrigin::class)
        ->set('timeFrame', $timeFrame)
        ->set('start', $start)
        ->set('end', $end)
        ->call('calculateChart')
        ->assertSet('labels', [])
        ->assertSet('series', [])
        ->assertOk()
        ->assertHasNoErrors();
});

test('timeframe this month', function (): void {
    $timeFrame = TimeFrameEnum::ThisMonth;

    Livewire::test(LeadsByRecordOrigin::class)
        ->set('timeFrame', $timeFrame)
        ->call('calculateChart')
        ->assertSet('labels', [
            $this->recordOrigins[1]->name,
            $this->recordOrigins[0]->name,
        ])
        ->assertSet('series', [
            getLeadsCountInTimeFrame($this->leads, $timeFrame, $this->recordOrigins[1]),
            getLeadsCountInTimeFrame($this->leads, $timeFrame, $this->recordOrigins[0]),
        ])
        ->assertOk()
        ->assertHasNoErrors();
});

test('timeframe this quarter', function (): void {
    $timeFrame = TimeFrameEnum::ThisQuarter;

    Livewire::test(LeadsByRecordOrigin::class)
        ->set('timeFrame', $timeFrame)
        ->call('calculateChart')
        ->assertSet('labels', [
            $this->recordOrigins[1]->name,
            $this->recordOrigins[0]->name,
        ])
        ->assertSet('series', [
            getLeadsCountInTimeFrame($this->leads, $timeFrame, $this->recordOrigins[1]),
            getLeadsCountInTimeFrame($this->leads, $timeFrame, $this->recordOrigins[0]),
        ])
        ->assertOk()
        ->assertHasNoErrors();
});

test('timeframe this week', function (): void {
    $timeFrame = TimeFrameEnum::ThisWeek;

    Livewire::test(LeadsByRecordOrigin::class)
        ->set('timeFrame', $timeFrame)
        ->call('calculateChart')
        ->assertSet('labels', [
            $this->recordOrigins[1]->name,
            $this->recordOrigins[0]->name,
        ])
        ->assertSet('series', [
            getLeadsCountInTimeFrame($this->leads, $timeFrame, $this->recordOrigins[1]),
            getLeadsCountInTimeFrame($this->leads, $timeFrame, $this->recordOrigins[0]),
        ])
        ->assertOk()
        ->assertHasNoErrors();
});

test('timeframe this year', function (): void {
    $timeFrame = TimeFrameEnum::ThisYear;

    Livewire::test(LeadsByRecordOrigin::class)
        ->set('timeFrame', $timeFrame)
        ->call('calculateChart')
        ->assertSet('labels', [
            $this->recordOrigins[1]->name,
            $this->recordOrigins[0]->name,
        ])
        ->assertSet('series', [
            getLeadsCountInTimeFrame($this->leads, $timeFrame, $this->recordOrigins[1]),
            getLeadsCountInTimeFrame($this->leads, $timeFrame, $this->recordOrigins[0]),
        ])
        ->assertOk()
        ->assertHasNoErrors();
});

test('timeframe today', function (): void {
    $timeFrame = TimeFrameEnum::Today;

    Livewire::test(LeadsByRecordOrigin::class)
        ->set('timeFrame', $timeFrame)
        ->call('calculateChart')
        ->assertSet('labels', [
            $this->recordOrigins[1]->name,
            $this->recordOrigins[0]->name,
        ])
        ->assertSet('series', [
            getLeadsCountInTimeFrame($this->leads, $timeFrame, $this->recordOrigins[1]),
            getLeadsCountInTimeFrame($this->leads, $timeFrame, $this->recordOrigins[0]),
        ])
        ->assertOk()
        ->assertHasNoErrors();
});

test('show method redirects correctly', function (): void {
    $params = [
        'id' => $this->recordOrigins[0]->getKey(),
        'label' => $this->recordOrigins[0]->name,
    ];

    Livewire::test(LeadsByRecordOrigin::class)
        ->call('show', $params)
        ->assertRedirect(route('sales.leads'));
});

function getLeadsCountInTimeFrame(Collection $leads, string $timeFrame, RecordOrigin $recordOrigin): int
{
    return $leads
        ->filter(
            fn (Lead $lead) => $lead->created_at->between(...TimeFrameEnum::getRange($timeFrame))
                && $lead->recordOrigin()
                    ->where('id', $recordOrigin->getKey())
                    ->where('is_active', true)
                    ->exists()
        )
        ->count();
}
