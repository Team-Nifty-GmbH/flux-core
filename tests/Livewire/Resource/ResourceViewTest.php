<?php

use FluxErp\Livewire\Resource\ResourceView;
use FluxErp\Models\Resource;
use FluxErp\Models\ResourceBooking;
use Livewire\Livewire;

test('resource view renders', function (): void {
    $resource = Resource::factory()->create();

    Livewire::test(ResourceView::class, ['id' => $resource->getKey()])
        ->assertOk();
});

test('startEdit sets edit to true', function (): void {
    $resource = Resource::factory()->create();

    Livewire::test(ResourceView::class, ['id' => $resource->getKey()])
        ->call('startEdit')
        ->assertSet('edit', true)
        ->assertOk();
});

test('cancel resets edit to false without TypeError', function (): void {
    $resource = Resource::factory()->create();

    Livewire::test(ResourceView::class, ['id' => $resource->getKey()])
        ->set('edit', true)
        ->call('cancel')
        ->assertSet('edit', false)
        ->assertOk();
});

test('save persists master data changes', function (): void {
    $resource = Resource::factory()->create(['name' => 'Old Name']);

    Livewire::test(ResourceView::class, ['id' => $resource->getKey()])
        ->set('resourceForm.name', 'New Name')
        ->call('save')
        ->assertReturned(true)
        ->assertOk();

    expect($resource->fresh()->name)->toBe('New Name');
});

test('saveBooking creates a booking', function (): void {
    $resource = Resource::factory()->create();

    Livewire::test(ResourceView::class, ['id' => $resource->getKey()])
        ->call('newBooking')
        ->set('resourceBookingForm.start', '2026-07-01T09:00')
        ->set('resourceBookingForm.end', '2026-07-01T11:00')
        ->call('saveBooking')
        ->assertReturned(true)
        ->assertOk();

    expect(
        ResourceBooking::query()
            ->where('resource_id', $resource->getKey())
            ->count()
    )->toBe(1);
});

test('saveBooking with overlapping slot creates no booking', function (): void {
    $resource = Resource::factory()->create(['allow_overbooking' => false]);
    ResourceBooking::factory()->create([
        'resource_id' => $resource->getKey(),
        'start' => '2026-07-01 09:00:00',
        'end' => '2026-07-01 11:00:00',
    ]);

    Livewire::test(ResourceView::class, ['id' => $resource->getKey()])
        ->call('newBooking')
        ->set('resourceBookingForm.start', '2026-07-01T10:00')
        ->set('resourceBookingForm.end', '2026-07-01T12:00')
        ->call('saveBooking')
        ->assertReturned(false)
        ->assertOk();

    expect(
        ResourceBooking::query()
            ->where('resource_id', $resource->getKey())
            ->count()
    )->toBe(1);
});

test('editBooking formats start and end for datetime-local input', function (): void {
    $resource = Resource::factory()->create();
    $booking = ResourceBooking::factory()->create([
        'resource_id' => $resource->getKey(),
        'start' => '2026-07-01 09:00:00',
        'end' => '2026-07-01 11:00:00',
    ]);

    Livewire::test(ResourceView::class, ['id' => $resource->getKey()])
        ->call('editBooking', $booking->getKey())
        ->assertSet('resourceBookingForm.start', '2026-07-01T09:00')
        ->assertSet('resourceBookingForm.end', '2026-07-01T11:00')
        ->assertOk();
});
