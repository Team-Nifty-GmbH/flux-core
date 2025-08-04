<?php

namespace FluxErp\Tests\Livewire\Widgets;

use Carbon\Carbon;
use FluxErp\Enums\TimeFrameEnum;
use FluxErp\Livewire\Widgets\LeadsByReferralSource;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Lead;
use FluxErp\Models\PriceList;
use FluxErp\Tests\Livewire\BaseSetup;
use Illuminate\Support\Collection;
use Livewire\Livewire;
use ReflectionProperty;

class LeadsByReferralSourceTest extends BaseSetup
{
    private Collection $addresses;

    private Collection $leads;

    protected function setUp(): void
    {
        parent::setUp();

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
    }

    public function test_options_return_expected_structure(): void
    {
        $test = Livewire::test(LeadsByReferralSource::class)
            ->set('timeFrame', TimeFrameEnum::ThisMonth)
            ->call('calculateChart');

        $options = $test->instance()->options();

        $this->assertIsArray($options);
        $this->assertNotEmpty($options);

        foreach ($options as $option) {
            $this->assertArrayHasKey('label', $option);
            $this->assertArrayHasKey('method', $option);
            $this->assertEquals('show', data_get($option, 'method'));
            $this->assertArrayHasKey('params', $option);
            $this->assertArrayHasKey('id', data_get($option, 'params'));
            $this->assertArrayHasKey('label', data_get($option, 'params'));
        }
    }

    public function test_options_use_data_correctly(): void
    {
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

        $this->assertIsArray($options);
        $this->assertCount(count($exampleData), $options);
        $this->assertEquals(data_get($this->addresses, '0.id'), data_get($options, '0.params.id'));
        $this->assertEquals(data_get($exampleData, '0.label'), data_get($options, '0.params.label'));
    }

    public function test_renders_successfully(): void
    {
        Livewire::test(LeadsByReferralSource::class)
            ->assertStatus(200);
    }

    public function test_show_method_redirects_correctly(): void
    {
        $params = [
            'id' => 1,
            'label' => 'Website',
        ];

        Livewire::test(LeadsByReferralSource::class)
            ->call('show', $params)
            ->assertRedirect(route('sales.leads'));
    }

    public function test_timeframe_in_the_future(): void
    {
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

    private function assertTimeframeResults(TimeFrameEnum $timeFrame): void
    {
        $test = Livewire::test(LeadsByReferralSource::class)
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

        foreach ($this->addresses as $address) {
            $expectedCount = $this->getLeadsCountInTimeFrame($timeFrame, $address);

            if ($expectedCount > 0) {
                $addressLabel = $address->getLabel();
                $index = array_search($addressLabel, $labels);

                if ($index !== false) {
                    $this->assertEquals($expectedCount, data_get($series, $index));
                }
            }
        }
    }

    private function getLeadsCountInTimeFrame(TimeFrameEnum $timeFrame, Address $address): int
    {
        return $this->leads
            ->filter(
                fn (Lead $lead) => $lead->recommended_by_address_id === $address->id
                    && $lead->created_at->between(...$timeFrame->getRange())
            )
            ->count();
    }
}
