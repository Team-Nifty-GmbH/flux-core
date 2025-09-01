<?php

uses(FluxErp\Tests\Livewire\BaseSetup::class);
use Carbon\Carbon;
use FluxErp\Enums\TimeFrameEnum;
use FluxErp\Livewire\Widgets\ContactsByContactOrigin;
use FluxErp\Models\Contact;
use FluxErp\Models\RecordOrigin;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->recordOrigins = collect([
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

    foreach ($this->recordOrigins as $recordOrigin) {
        $quantity = $recordOrigin->id === $this->recordOrigins[1]->id ? 2 : 1;

        $this->contacts = $this->contacts
            ->merge(
                Contact::factory()->count($quantity)->create([
                    'client_id' => $this->dbClient->getKey(),
                    'record_origin_id' => $recordOrigin->id,
                ])
            )
            ->merge(
                Contact::factory()->count($quantity)->create([
                    'client_id' => $this->dbClient->getKey(),
                    'record_origin_id' => $recordOrigin->id,
                    'created_at' => Carbon::now()->startOfWeek(),
                ])
            )
            ->merge(
                Contact::factory()->count($quantity)->create([
                    'client_id' => $this->dbClient->getKey(),
                    'record_origin_id' => $recordOrigin->id,
                    'created_at' => Carbon::now()->startOfMonth(),
                ])
            )
            ->merge(
                Contact::factory()->count($quantity)->create([
                    'client_id' => $this->dbClient->getKey(),
                    'record_origin_id' => $recordOrigin->id,
                    'created_at' => Carbon::now()->startOfQuarter(),
                ])
            )
            ->merge(
                Contact::factory()->count($quantity)->create([
                    'client_id' => $this->dbClient->getKey(),
                    'record_origin_id' => $recordOrigin->id,
                    'created_at' => Carbon::now()->startOfYear(),
                ])
            );
    }
});

test('renders successfully', function (): void {
    Livewire::test(ContactsByContactOrigin::class)
        ->assertStatus(200);
});

test('timeframe in the future', function (): void {
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
});

test('timeframe this month', function (): void {
    $timeFrame = TimeFrameEnum::ThisMonth;

    Livewire::test(ContactsByContactOrigin::class)
        ->set('timeFrame', $timeFrame)
        ->call('calculateChart')
        ->assertSet('labels', [
            $this->recordOrigins[1]->name,
            $this->recordOrigins[0]->name,
        ])
        ->assertSet('series', [
            getContactsCountInTimeFrame($timeFrame, $this->recordOrigins[1]),
            getContactsCountInTimeFrame($timeFrame, $this->recordOrigins[0]),
        ])
        ->assertStatus(200)
        ->assertHasNoErrors();
});

test('timeframe this quarter', function (): void {
    $timeFrame = TimeFrameEnum::ThisQuarter;

    Livewire::test(ContactsByContactOrigin::class)
        ->set('timeFrame', $timeFrame)
        ->call('calculateChart')
        ->assertSet('labels', [
            $this->recordOrigins[1]->name,
            $this->recordOrigins[0]->name,
        ])
        ->assertSet('series', [
            getContactsCountInTimeFrame($timeFrame, $this->recordOrigins[1]),
            getContactsCountInTimeFrame($timeFrame, $this->recordOrigins[0]),
        ])
        ->assertStatus(200)
        ->assertHasNoErrors();
});

test('timeframe this week', function (): void {
    $timeFrame = TimeFrameEnum::ThisWeek;

    Livewire::test(ContactsByContactOrigin::class)
        ->set('timeFrame', $timeFrame)
        ->call('calculateChart')
        ->assertSet('labels', [
            $this->recordOrigins[1]->name,
            $this->recordOrigins[0]->name,
        ])
        ->assertSet('series', [
            getContactsCountInTimeFrame($timeFrame, $this->recordOrigins[1]),
            getContactsCountInTimeFrame($timeFrame, $this->recordOrigins[0]),
        ])
        ->assertStatus(200)
        ->assertHasNoErrors();
});

test('timeframe this year', function (): void {
    $timeFrame = TimeFrameEnum::ThisYear;

    Livewire::test(ContactsByContactOrigin::class)
        ->set('timeFrame', $timeFrame)
        ->call('calculateChart')
        ->assertSet('labels', [
            $this->recordOrigins[1]->name,
            $this->recordOrigins[0]->name,
        ])
        ->assertSet('series', [
            getContactsCountInTimeFrame($timeFrame, $this->recordOrigins[1]),
            getContactsCountInTimeFrame($timeFrame, $this->recordOrigins[0]),
        ])
        ->assertStatus(200)
        ->assertHasNoErrors();
});

test('timeframe today', function (): void {
    $timeFrame = TimeFrameEnum::Today;

    Livewire::test(ContactsByContactOrigin::class)
        ->set('timeFrame', $timeFrame)
        ->call('calculateChart')
        ->assertSet('labels', [
            $this->recordOrigins[1]->name,
            $this->recordOrigins[0]->name,
        ])
        ->assertSet('series', [
            getContactsCountInTimeFrame($timeFrame, $this->recordOrigins[1]),
            getContactsCountInTimeFrame($timeFrame, $this->recordOrigins[0]),
        ])
        ->assertStatus(200)
        ->assertHasNoErrors();
});

function getContactsCountInTimeFrame(TimeFrameEnum $timeFrame, RecordOrigin $recordOrigin): int
{
    return $this->contacts
        ->filter(
            fn (Contact $contact) => $contact->created_at->between(...$timeFrame->getRange())
                && $contact->recordOrigin()
                    ->where('id', $recordOrigin->id)
                    ->where('is_active', true)
                    ->exists()
        )
        ->count();
}
