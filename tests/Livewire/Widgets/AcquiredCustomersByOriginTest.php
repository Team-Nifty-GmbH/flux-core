<?php

namespace FluxErp\Tests\Livewire\Widgets;

use Carbon\Carbon;
use FluxErp\Enums\TimeFrameEnum;
use FluxErp\Livewire\Widgets\AcquiredCustomersByOrigin;
use FluxErp\Models\Contact;
use FluxErp\Models\ContactOrigin;
use FluxErp\Tests\Livewire\BaseSetup;
use Illuminate\Support\Collection;
use Livewire\Livewire;

class AcquiredCustomersByOriginTest extends BaseSetup
{
    private Collection $contactCollection;

    private Collection $contactOriginCollection;

    protected function setUp(): void
    {
        parent::setUp();

        $now = Carbon::now();

        $contactOriginActive = ContactOrigin::factory()->create([
            'name' => 'testOrigin1',
            'is_active' => true,
        ]);

        $contactOriginInactive = ContactOrigin::factory()->create([
            'name' => 'testOrigin2',
            'is_active' => false,
        ]);

        $this->contactOriginCollection = collect([
            $contactOriginActive->id => $contactOriginActive,
            $contactOriginInactive->id => $contactOriginInactive,
        ]);

        $this->contactCollection = collect([
            Contact::factory()->create([
                'client_id' => $this->dbClient->getKey(),
                'contact_origin_id' => $contactOriginActive->id,
                'customer_number' => 5551,
            ]),
            Contact::factory()->create([
                'client_id' => $this->dbClient->getKey(),
                'contact_origin_id' => $contactOriginActive->id,
                'customer_number' => 5552,
                'created_at' => $now->startOfWeek(),
            ]),
            Contact::factory()->create([
                'client_id' => $this->dbClient->getKey(),
                'contact_origin_id' => $contactOriginActive->id,
                'customer_number' => 5553,
                'created_at' => $now->startOfMonth(),
            ]),
            Contact::factory()->create([
                'client_id' => $this->dbClient->getKey(),
                'contact_origin_id' => $contactOriginActive->id,
                'customer_number' => 5554,
                'created_at' => $now->startOfQuarter(),
            ]),
            Contact::factory()->create([
                'client_id' => $this->dbClient->getKey(),
                'contact_origin_id' => $contactOriginActive->id,
                'customer_number' => 5555,
                'created_at' => $now->startOfYear(),
            ]),
            Contact::factory()->create([
                'client_id' => $this->dbClient->getKey(),
                'contact_origin_id' => $contactOriginInactive->id,
                'customer_number' => 5556,
            ]),
        ]);
    }

    private function getNumberOfContactsWithSameDate(Contact $inputContact): int
    {
        $sameDateContacts = -1;

        foreach ($this->contactCollection as $contact) {
            if ($inputContact->created_at == $contact->created_at &&
                $this->contactOriginCollection->get($contact->contact_origin_id)->is_active) {
                $sameDateContacts++;
            }
        }

        return $sameDateContacts;
    }

    public function test_renders_successfully()
    {
        Livewire::test(AcquiredCustomersByOrigin::class)
            ->assertStatus(200);
    }

    public function test_timeframe_today()
    {
        $timeFrame = TimeFrameEnum::Today;

        Livewire::test(AcquiredCustomersByOrigin::class)
            ->set('timeFrame', $timeFrame)
            ->call('calculateChart')
            ->assertSet('labels', [0 => 'testOrigin1'])
            ->assertSet('series', [0 => 1 + $this->getNumberOfContactsWithSameDate($this->contactCollection[0])])
            ->assertStatus(200)
            ->assertHasNoErrors();
    }

    public function test_timeframe_this_week()
    {
        $timeFrame = TimeFrameEnum::ThisWeek;

        Livewire::test(AcquiredCustomersByOrigin::class)
            ->set('timeFrame', $timeFrame)
            ->call('calculateChart')
            ->assertSet('labels', [0 => 'testOrigin1'])
            ->assertSet('series', [0 => 2 + $this->getNumberOfContactsWithSameDate($this->contactCollection[1])])
            ->assertStatus(200)
            ->assertHasNoErrors();
    }

    public function test_timeframe_this_month()
    {
        $timeFrame = TimeFrameEnum::ThisMonth;

        Livewire::test(AcquiredCustomersByOrigin::class)
            ->set('timeFrame', $timeFrame)
            ->call('calculateChart')
            ->assertSet('labels', [0 => 'testOrigin1'])
            ->assertSet('series', [0 => 3 + $this->getNumberOfContactsWithSameDate($this->contactCollection[2])])
            ->assertStatus(200)
            ->assertHasNoErrors();
    }

    public function test_timeframe_this_quarter()
    {
        $timeFrame = TimeFrameEnum::ThisQuarter;

        Livewire::test(AcquiredCustomersByOrigin::class)
            ->set('timeFrame', $timeFrame)
            ->call('calculateChart')
            ->assertSet('labels', [0 => 'testOrigin1'])
            ->assertSet('series', [0 => 4 + $this->getNumberOfContactsWithSameDate($this->contactCollection[3])])
            ->assertStatus(200)
            ->assertHasNoErrors();
    }

    public function test_timeframe_this_year()
    {
        $timeFrame = TimeFrameEnum::ThisYear;

        Livewire::test(AcquiredCustomersByOrigin::class)
            ->set('timeFrame', $timeFrame)
            ->call('calculateChart')
            ->assertSet('labels', [0 => 'testOrigin1'])
            ->assertSet('series', [0 => 4 + $this->getNumberOfContactsWithSameDate($this->contactCollection[4])])
            ->assertStatus(200)
            ->assertHasNoErrors();
    }

    public function test_timeframe_in_the_future()
    {
        $start = Carbon::now()->addDay();
        $end = Carbon::now()->addDays(2);
        $timeFrame = TimeFrameEnum::Custom;

        Livewire::test(AcquiredCustomersByOrigin::class)
            ->set('timeFrame', $timeFrame)
            ->set('start', $start)
            ->set('end', $end)
            ->call('calculateChart')
            ->assertSet('labels', [])
            ->assertSet('series', [])
            ->assertStatus(200)
            ->assertHasNoErrors();
    }
}
