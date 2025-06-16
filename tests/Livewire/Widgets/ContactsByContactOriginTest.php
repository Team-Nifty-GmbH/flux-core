<?php

namespace FluxErp\Tests\Livewire\Widgets;

use Carbon\Carbon;
use FluxErp\Enums\TimeFrameEnum;
use FluxErp\Livewire\Widgets\ContactsByContactOrigin;
use FluxErp\Models\Contact;
use FluxErp\Models\RecordOrigin;
use FluxErp\Tests\Livewire\BaseSetup;
use Illuminate\Support\Collection;
use Livewire\Livewire;

class ContactsByContactOriginTest extends BaseSetup
{
    private Collection $contactOrigins;

    private Collection $contacts;

    protected function setUp(): void
    {
        parent::setUp();

        $this->contactOrigins = collect([
            RecordOrigin::factory()->create([
                'name' => 'testOrigin1',
                'is_active' => true,
                'model_type' => morph_alias(Contact::class),
            ]),
            RecordOrigin::factory()->create([
                'name' => 'testOrigin2',
                'is_active' => true,
                'model_type' => morph_alias(Contact::class),
            ]),
            RecordOrigin::factory()->create([
                'name' => 'testOrigin3',
                'is_active' => false,
                'model_type' => morph_alias(Contact::class),
            ]),
        ]);

        $this->contacts = collect();

        foreach ($this->contactOrigins as $contactOrigin) {
            $quantity = $contactOrigin->id === $this->contactOrigins[1]->id ? 2 : 1;

            $this->contacts = $this->contacts
                ->merge(
                    Contact::factory()->count($quantity)->create([
                        'client_id' => $this->dbClient->getKey(),
                        'record_origin_id' => $contactOrigin->id,
                    ])
                )
                ->merge(
                    Contact::factory()->count($quantity)->create([
                        'client_id' => $this->dbClient->getKey(),
                        'record_origin_id' => $contactOrigin->id,
                        'created_at' => Carbon::now()->startOfWeek(),
                    ])
                )
                ->merge(
                    Contact::factory()->count($quantity)->create([
                        'client_id' => $this->dbClient->getKey(),
                        'record_origin_id' => $contactOrigin->id,
                        'created_at' => Carbon::now()->startOfMonth(),
                    ])
                )
                ->merge(
                    Contact::factory()->count($quantity)->create([
                        'client_id' => $this->dbClient->getKey(),
                        'record_origin_id' => $contactOrigin->id,
                        'created_at' => Carbon::now()->startOfQuarter(),
                    ])
                )
                ->merge(
                    Contact::factory()->count($quantity)->create([
                        'client_id' => $this->dbClient->getKey(),
                        'record_origin_id' => $contactOrigin->id,
                        'created_at' => Carbon::now()->startOfYear(),
                    ])
                );
        }
    }

    public function test_renders_successfully(): void
    {
        Livewire::test(ContactsByContactOrigin::class)
            ->assertStatus(200);
    }

    public function test_timeframe_in_the_future(): void
    {
        $start = Carbon::now()->addDay();
        $end = Carbon::now()->addDays(2);
        $timeFrame = TimeFrameEnum::Custom;

        Livewire::test(ContactsByContactOrigin::class)
            ->set('timeFrame', $timeFrame)
            ->set('start', $start)
            ->set('end', $end)
            ->call('calculateChart')
            ->assertSet('labels', [])
            ->assertSet('series', [])
            ->assertStatus(200)
            ->assertHasNoErrors();
    }

    public function test_timeframe_this_month(): void
    {
        $timeFrame = TimeFrameEnum::ThisMonth;

        Livewire::test(ContactsByContactOrigin::class)
            ->set('timeFrame', $timeFrame)
            ->call('calculateChart')
            ->assertSet('labels', [
                $this->contactOrigins[1]->name,
                $this->contactOrigins[0]->name,
            ])
            ->assertSet('series', [
                $this->getContactsCountInTimeFrame($timeFrame, $this->contactOrigins[1]),
                $this->getContactsCountInTimeFrame($timeFrame, $this->contactOrigins[0]),
            ])
            ->assertStatus(200)
            ->assertHasNoErrors();
    }

    public function test_timeframe_this_quarter(): void
    {
        $timeFrame = TimeFrameEnum::ThisQuarter;

        Livewire::test(ContactsByContactOrigin::class)
            ->set('timeFrame', $timeFrame)
            ->call('calculateChart')
            ->assertSet('labels', [
                $this->contactOrigins[1]->name,
                $this->contactOrigins[0]->name,
            ])
            ->assertSet('series', [
                $this->getContactsCountInTimeFrame($timeFrame, $this->contactOrigins[1]),
                $this->getContactsCountInTimeFrame($timeFrame, $this->contactOrigins[0]),
            ])
            ->assertStatus(200)
            ->assertHasNoErrors();
    }

    public function test_timeframe_this_week(): void
    {
        $timeFrame = TimeFrameEnum::ThisWeek;

        Livewire::test(ContactsByContactOrigin::class)
            ->set('timeFrame', $timeFrame)
            ->call('calculateChart')
            ->assertSet('labels', [
                $this->contactOrigins[1]->name,
                $this->contactOrigins[0]->name,
            ])
            ->assertSet('series', [
                $this->getContactsCountInTimeFrame($timeFrame, $this->contactOrigins[1]),
                $this->getContactsCountInTimeFrame($timeFrame, $this->contactOrigins[0]),
            ])
            ->assertStatus(200)
            ->assertHasNoErrors();
    }

    public function test_timeframe_this_year(): void
    {
        $timeFrame = TimeFrameEnum::ThisYear;

        Livewire::test(ContactsByContactOrigin::class)
            ->set('timeFrame', $timeFrame)
            ->call('calculateChart')
            ->assertSet('labels', [
                $this->contactOrigins[1]->name,
                $this->contactOrigins[0]->name,
            ])
            ->assertSet('series', [
                $this->getContactsCountInTimeFrame($timeFrame, $this->contactOrigins[1]),
                $this->getContactsCountInTimeFrame($timeFrame, $this->contactOrigins[0]),
            ])
            ->assertStatus(200)
            ->assertHasNoErrors();
    }

    public function test_timeframe_today(): void
    {
        $timeFrame = TimeFrameEnum::Today;

        Livewire::test(ContactsByContactOrigin::class)
            ->set('timeFrame', $timeFrame)
            ->call('calculateChart')
            ->assertSet('labels', [
                $this->contactOrigins[1]->name,
                $this->contactOrigins[0]->name,
            ])
            ->assertSet('series', [
                $this->getContactsCountInTimeFrame($timeFrame, $this->contactOrigins[1]),
                $this->getContactsCountInTimeFrame($timeFrame, $this->contactOrigins[0]),
            ])
            ->assertStatus(200)
            ->assertHasNoErrors();
    }

    private function getContactsCountInTimeFrame(TimeFrameEnum $timeFrame, RecordOrigin $contactOrigin): int
    {
        return $this->contacts
            ->filter(
                fn (Contact $contact) => $contact->created_at->between(...$timeFrame->getRange())
                    && $contact->contactOrigin()
                        ->where('is_active', true)
                        ->where('id', $contactOrigin->id)
                        ->exists()
            )
            ->count();
    }
}
