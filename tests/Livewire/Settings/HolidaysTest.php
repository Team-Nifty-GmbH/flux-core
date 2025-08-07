<?php

namespace FluxErp\Tests\Livewire\Settings;

use FluxErp\Livewire\Settings\Holidays;
use FluxErp\Models\Client;
use FluxErp\Models\Holiday;
use FluxErp\Models\Language;
use FluxErp\Models\Location;
use FluxErp\Tests\Livewire\BaseSetup;
use Livewire\Livewire;

class HolidaysTest extends BaseSetup
{
    protected string $livewireComponent = Holidays::class;
    
    protected ?Client $client = null;
    protected ?Location $location = null;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create language and client for testing
        $language = Language::find(1) ?? Language::factory()->create(['id' => 1]);
        $this->client = Client::find(1) ?? Client::factory()->create(['id' => 1, 'is_active' => true]);
        
        // Create a location for testing
        $this->location = Location::factory()->create([
            'name' => 'Test Location',
            'is_active' => true,
            'client_id' => 1
        ]);
    }

    public function test_renders_successfully(): void
    {
        Livewire::test($this->livewireComponent)
            ->assertStatus(200);
    }

    public function test_can_create_holiday(): void
    {
        Livewire::test($this->livewireComponent)
            ->call('edit')
            ->set('holidayForm.name', 'Test Holiday')
            ->set('holidayForm.date', now()->addMonth()->format('Y-m-d'))
            ->set('holidayForm.is_half_day', false)
            ->set('holidayForm.is_recurring', false)
            ->set('holidayForm.location_id', $this->location->id)
            ->set('holidayForm.client_id', 1)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('holidays', [
            'name' => 'Test Holiday',
            'location_id' => $this->location->id
        ]);
    }

    public function test_can_edit_existing_holiday(): void
    {
        $holiday = Holiday::factory()->create([
            'name' => 'Original Holiday',
            'location_id' => $this->location->id,
            'client_id' => 1
        ]);

        Livewire::test($this->livewireComponent)
            ->call('edit', $holiday->id)
            ->assertSet('holidayForm.name', 'Original Holiday')
            ->set('holidayForm.name', 'Updated Holiday')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('holidays', [
            'id' => $holiday->id,
            'name' => 'Updated Holiday'
        ]);
    }

    public function test_can_delete_holiday(): void
    {
        $holiday = Holiday::factory()->create([
            'location_id' => $this->location->id,
            'client_id' => 1
        ]);

        Livewire::test($this->livewireComponent)
            ->call('delete', $holiday->id);

        $this->assertSoftDeleted('holidays', ['id' => $holiday->id]);
    }

    public function test_validates_required_fields(): void
    {
        $initialCount = Holiday::count();
        
        Livewire::test($this->livewireComponent)
            ->call('edit')
            ->set('holidayForm.name', '')
            ->set('holidayForm.date', null)
            ->call('save');
            
        // Verify no holiday was created due to validation failure
        $this->assertEquals($initialCount, Holiday::count());
    }

    public function test_can_create_recurring_holiday(): void
    {
        Livewire::test($this->livewireComponent)
            ->call('edit')
            ->set('holidayForm.name', 'Christmas')
            ->set('holidayForm.date', '2025-12-25')
            ->set('holidayForm.is_recurring', true)
            ->set('holidayForm.location_id', $this->location->id)
            ->set('holidayForm.client_id', 1)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('holidays', [
            'name' => 'Christmas',
            'is_recurring' => true
        ]);
    }

    public function test_can_create_half_day_holiday(): void
    {
        Livewire::test($this->livewireComponent)
            ->call('edit')
            ->set('holidayForm.name', 'Half Day Holiday')
            ->set('holidayForm.date', now()->addMonth()->format('Y-m-d'))
            ->set('holidayForm.is_half_day', true)
            ->set('holidayForm.location_id', $this->location->id)
            ->set('holidayForm.client_id', 1)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('holidays', [
            'name' => 'Half Day Holiday',
            'is_half_day' => true
        ]);
    }
}