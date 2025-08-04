<?php

namespace FluxErp\Tests\Livewire\Widgets;

use Carbon\Carbon;
use FluxErp\Enums\TimeFrameEnum;
use FluxErp\Livewire\Widgets\WonLeadsBySalesRepresentative;
use FluxErp\Models\Language;
use FluxErp\Models\Lead;
use FluxErp\Models\LeadState;
use FluxErp\Models\User;
use FluxErp\Tests\Livewire\BaseSetup;
use Illuminate\Support\Collection;
use Livewire\Livewire;
use ReflectionProperty;

class WonLeadsBySalesRepresentativeTest extends BaseSetup
{
    private Collection $leads;

    private Collection $users;

    private LeadState $wonLeadState;

    protected function setUp(): void
    {
        parent::setUp();

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
            $quantity = $user->id === $this->users[1]->id ? 2 : 1;

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
    }

    public function test_options_returns_empty_array_when_series_is_null(): void
    {
        $test = Livewire::test(WonLeadsBySalesRepresentative::class);

        $instance = $test->instance();
        $ref = new ReflectionProperty($instance, 'series');
        $ref->setValue($instance, null);

        $test->tap(function ($test): void {
            $result = $test->instance()->options();
            $this->assertIsArray($result);
            $this->assertEmpty($result);
        });
    }

    public function test_options_returns_expected_structure(): void
    {
        $test = Livewire::test(WonLeadsBySalesRepresentative::class)
            ->set('timeFrame', TimeFrameEnum::ThisMonth)
            ->call('calculateChart');

        $options = $test->instance()->options();

        $this->assertIsArray($options);
        $this->assertNotEmpty($options);

        $expectedLabels = $this->users->pluck('name')->toArray();

        foreach ($options as $option) {
            $this->assertArrayHasKey('label', $option);
            $this->assertArrayHasKey('method', $option);
            $this->assertEquals('show', $option['method']);
            $this->assertArrayHasKey('params', $option);
            $this->assertArrayHasKey('id', $option['params']);
            $this->assertArrayHasKey('name', $option['params']);

            $this->assertContains($option['label'], $expectedLabels);
        }

        // First option should be the one with the most leads
        $this->assertEquals($this->users[1]->getLabel(), $options[0]['label']);
    }

    public function test_options_uses_series_data_correctly(): void
    {
        $example_series = [
            [
                'id' => $this->users[0]->id,
                'name' => 'Test Sales Rep',
                'color' => '#123456',
                'data' => [5],
            ],
        ];

        $test = Livewire::test(WonLeadsBySalesRepresentative::class);

        $instance = $test->instance();
        $reflection = new ReflectionProperty($instance, 'series');
        $reflection->setValue($instance, $example_series);

        $options = $instance->options();

        $this->assertIsArray($options);
        $this->assertCount(1, $options);
        $this->assertEquals('Test Sales Rep', $options[0]['label']);
        $this->assertEquals($this->users[0]->id, $options[0]['params']['id']);
        $this->assertEquals('Test Sales Rep', $options[0]['params']['name']);
    }

    public function test_renders_successfully(): void
    {
        Livewire::test(WonLeadsBySalesRepresentative::class)
            ->assertStatus(200);
    }

    public function test_show_method_redirects_correctly(): void
    {
        $params = [
            'id' => 1,
            'name' => 'Sales Rep 1',
        ];

        Livewire::test(WonLeadsBySalesRepresentative::class)
            ->call('show', $params)
            ->assertRedirect(route('sales.leads'));
    }

    public function test_timeframe_in_the_future(): void
    {
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

    protected function getWonLeadsCountInTimeFrame(TimeFrameEnum $timeFrame, User $user): int
    {
        return $this->leads
            ->filter(
                fn (Lead $lead) => $lead->end->between(...$timeFrame->getRange())
                    && $lead->user_id === $user->id
                    && $lead->leadState->is_won === true
            )
            ->count();
    }

    private function assertTimeframeResults(TimeFrameEnum $timeFrame): void
    {
        $test = Livewire::test(WonLeadsBySalesRepresentative::class)
            ->set('timeFrame', $timeFrame)
            ->call('calculateChart')
            ->assertStatus(200)
            ->assertHasNoErrors();

        $series = $test->get('series');
        $this->assertIsArray($series);
        $this->assertNotEmpty($series);

        // Verify sales rep 2 has more leads than the other reps
        $this->assertEquals($this->users[1]->getLabel(), $series[0]['name']);
        $this->assertEquals($this->getWonLeadsCountInTimeFrame($timeFrame, $this->users[1]), $series[0]['data'][0]);
    }
}
