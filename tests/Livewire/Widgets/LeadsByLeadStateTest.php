<?php

namespace FluxErp\Tests\Livewire\Widgets;

use Carbon\Carbon;
use FluxErp\Enums\TimeFrameEnum;
use FluxErp\Livewire\Widgets\LeadsByLeadState;
use FluxErp\Models\Lead;
use FluxErp\Models\LeadState;
use FluxErp\Tests\Livewire\BaseSetup;
use Illuminate\Support\Collection;
use Livewire\Livewire;
use ReflectionProperty;

class LeadsByLeadStateTest extends BaseSetup
{
    private Collection $leads;

    private Collection $leadStates;

    protected function setUp(): void
    {
        parent::setUp();

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
            $quantity = $leadState->id === $this->leadStates[1]->id ? 2 : 1;

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
    }

    public function test_options_returns_expected_structure(): void
    {
        $test = Livewire::test(LeadsByLeadState::class)
            ->set('timeFrame', TimeFrameEnum::ThisMonth)
            ->call('calculateChart');

        $options = $test->instance()->options();

        $this->assertIsArray($options);
        $this->assertNotEmpty($options);

        foreach ($options as $option) {
            $this->assertArrayHasKey('label', $option);
            $this->assertArrayHasKey('method', $option);
            $this->assertEquals('show', $option['method']);
            $this->assertArrayHasKey('params', $option);
            $this->assertArrayHasKey('id', $option['params']);
            $this->assertArrayHasKey('name', $option['params']);
        }

        // First option should be for the lead state with the most leads
        $this->assertTrue(str_contains($options[1]['label'], $this->leadStates[1]->name));
    }

    public function test_options_uses_data_correctly(): void
    {
        $example_data = [
            [
                'id' => $this->leadStates[0]->id,
                'name' => 'Test Lead State',
                'color' => '#123456',
                'total' => 5,
            ],
        ];

        $test = Livewire::test(LeadsByLeadState::class);

        $instance = $test->instance();
        $reflection = new ReflectionProperty($instance, 'data');
        $reflection->setValue($instance, $example_data);

        $options = $instance->options();

        $this->assertIsArray($options);
        $this->assertCount(1, $options);
        $this->assertEquals($this->leadStates[0]->id, $options[0]['params']['id']);
        $this->assertEquals('Test Lead State', $options[0]['params']['name']);
    }

    public function test_renders_successfully(): void
    {
        Livewire::test(LeadsByLeadState::class)
            ->assertStatus(200);
    }

    public function test_show_method_redirects_correctly(): void
    {
        $params = [
            'id' => 1,
            'name' => 'New',
        ];

        Livewire::test(LeadsByLeadState::class)
            ->call('show', $params)
            ->assertRedirect(route('sales.leads'));
    }

    public function test_timeframe_in_the_future(): void
    {
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
    }

    public function test_timeframe_this_month(): void
    {
        $this->assertTimeframeResults(TimeFrameEnum::ThisMonth);
    }

    public function test_timeframe_this_quarter(): void
    {
        $this->assertTimeframeResults(TimeFrameEnum::ThisQuarter);
    }

    public function test_timeframe_this_week(): void
    {
        $this->assertTimeframeResults(TimeFrameEnum::ThisWeek);
    }

    public function test_timeframe_this_year(): void
    {
        $this->assertTimeframeResults(TimeFrameEnum::ThisYear);
    }

    public function test_timeframe_today(): void
    {
        $this->assertTimeframeResults(TimeFrameEnum::Today);
    }

    protected function getLeadsCountInTimeFrame(TimeFrameEnum $timeFrame, LeadState $leadState): int
    {
        return $this->leads
            ->filter(
                fn (Lead $lead) => $lead->created_at->between(...$timeFrame->getRange())
                    && $lead->lead_state_id === $leadState->id
            )
            ->count();
    }

    private function assertTimeframeResults(TimeFrameEnum $timeFrame): void
    {
        $test = Livewire::test(LeadsByLeadState::class)
            ->set('timeFrame', $timeFrame)
            ->call('calculateChart')
            ->assertStatus(200)
            ->assertHasNoErrors();

        $series = $test->get('series');
        $labels = $test->get('labels');
        $data = $test->get('data');

        $this->assertIsArray($series);
        $this->assertIsArray($labels);
        $this->assertIsArray($data);
        $this->assertNotEmpty($series);
        $this->assertNotEmpty($labels);
        $this->assertNotEmpty($data);

        // Verify "In Progress" has more leads than other states
        $leadStateTotals = array_column($data, 'total', 'name');
        $inProgressTotal = $leadStateTotals[$this->leadStates[1]->name] ?? 0;

        foreach ($leadStateTotals as $state => $total) {
            if ($state !== $this->leadStates[1]->name) {
                $this->assertGreaterThanOrEqual($total, $inProgressTotal);
            }
        }

        // Verify counts match expected values
        foreach ($this->leadStates as $leadState) {
            $expectedCount = $this->getLeadsCountInTimeFrame($timeFrame, $leadState);
            $index = array_search($leadState->name, $labels);

            if ($index !== false && $expectedCount > 0) {
                $this->assertEquals($expectedCount, $series[$index]);
            }
        }
    }
}
