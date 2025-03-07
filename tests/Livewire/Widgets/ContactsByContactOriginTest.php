<?php

namespace FluxErp\Tests\Livewire\Widgets;

use Carbon\Carbon;
use FluxErp\Enums\TimeFrameEnum;
use FluxErp\Livewire\Widgets\ContactsByContactOrigin;
use FluxErp\Models\Contact;
use FluxErp\Models\ContactOrigin;
use FluxErp\Tests\Livewire\BaseSetup;
use Illuminate\Support\Collection;
use Livewire\Livewire;

class ContactsByContactOriginTest extends BaseSetup
{
    private Collection $contacts;

    private Collection $contactOrigins;

    protected function setUp(): void
    {
        parent::setUp();

        $now = Carbon::now();

        $this->contactOrigins = collect([
            ContactOrigin::factory()->create([
                'name' => 'testOrigin1',
                'is_active' => true,
            ]),
            ContactOrigin::factory()->create([
                'name' => 'testOrigin2',
                'is_active' => false,
            ]),
        ]);

        $this->contacts = collect([
            Contact::factory()->create([
                'client_id' => $this->dbClient->getKey(),
                'contact_origin_id' => $this->contactOrigins->first()->id,
                'customer_number' => 5551,
            ]),
            Contact::factory()->create([
                'client_id' => $this->dbClient->getKey(),
                'contact_origin_id' => $this->contactOrigins->first()->id,
                'customer_number' => 5552,
                'created_at' => $now->startOfWeek(),
            ]),
            Contact::factory()->create([
                'client_id' => $this->dbClient->getKey(),
                'contact_origin_id' => $this->contactOrigins->first()->id,
                'customer_number' => 5553,
                'created_at' => $now->startOfMonth(),
            ]),
            Contact::factory()->create([
                'client_id' => $this->dbClient->getKey(),
                'contact_origin_id' => $this->contactOrigins->first()->id,
                'customer_number' => 5554,
                'created_at' => $now->startOfQuarter(),
            ]),
            Contact::factory()->create([
                'client_id' => $this->dbClient->getKey(),
                'contact_origin_id' => $this->contactOrigins->first()->id,
                'customer_number' => 5555,
                'created_at' => $now->startOfYear(),
            ]),
            Contact::factory()->create([
                'client_id' => $this->dbClient->getKey(),
                'contact_origin_id' => $this->contactOrigins->last()->id,
                'customer_number' => 5556,
            ]),
        ]);
    }

    public function test_renders_successfully()
    {
        Livewire::test(ContactsByContactOrigin::class)
            ->assertStatus(200);
    }

    public function test_timeframe_today()
    {
        $timeFrame = TimeFrameEnum::Today;

        Livewire::test(ContactsByContactOrigin::class)
            ->set('timeFrame', $timeFrame)
            ->call('calculateChart')
            ->assertSet('labels', [
                $this->contactOrigins->first()->name
            ])
            ->assertSet('series', [
                $this->getContactsCountInTimeFrame($timeFrame)
            ])
            ->assertStatus(200)
            ->assertHasNoErrors();
    }

    public function test_timeframe_this_week()
    {
        $timeFrame = TimeFrameEnum::ThisWeek;

        Livewire::test(ContactsByContactOrigin::class)
            ->set('timeFrame', $timeFrame)
            ->call('calculateChart')
            ->assertSet('labels', [
                $this->contactOrigins->first()->name]
            )
            ->assertSet('series', [
                $this->getContactsCountInTimeFrame($timeFrame)
            ])
            ->assertStatus(200)
            ->assertHasNoErrors();
    }

    public function test_timeframe_this_month()
    {
        $timeFrame = TimeFrameEnum::ThisMonth;

        Livewire::test(ContactsByContactOrigin::class)
            ->set('timeFrame', $timeFrame)
            ->call('calculateChart')
            ->assertSet('labels', [
                $this->contactOrigins->first()->name
            ])
            ->assertSet('series', [
                $this->getContactsCountInTimeFrame($timeFrame)
            ])
            ->assertStatus(200)
            ->assertHasNoErrors();
    }

    public function test_timeframe_this_quarter()
    {
        $timeFrame = TimeFrameEnum::ThisQuarter;

        Livewire::test(ContactsByContactOrigin::class)
            ->set('timeFrame', $timeFrame)
            ->call('calculateChart')
            ->assertSet('labels', [
                $this->contactOrigins->first()->name
            ])
            ->assertSet('series', [
                $this->getContactsCountInTimeFrame($timeFrame)
            ])
            ->assertStatus(200)
            ->assertHasNoErrors();
    }

    public function test_timeframe_this_year()
    {
        $timeFrame = TimeFrameEnum::ThisYear;

        Livewire::test(ContactsByContactOrigin::class)
            ->set('timeFrame', $timeFrame)
            ->call('calculateChart')
            ->assertSet('labels', [
                $this->contactOrigins->first()->name
            ])
            ->assertSet('series', [
                $this->getContactsCountInTimeFrame($timeFrame)
            ])
            ->assertStatus(200)
            ->assertHasNoErrors();
    }

    public function test_timeframe_in_the_future()
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

    private function getContactsCountInTimeFrame(TimeFrameEnum $timeFrame): int
    {
        return $this->contacts
            ->filter(
                fn (Contact $contact) => $contact->created_at->between(...$timeFrame->getRange())
                    && $contact->contactOrigin()
                        ->where('is_active', true)
                        ->exists()
            )
            ->count();
    }
}
