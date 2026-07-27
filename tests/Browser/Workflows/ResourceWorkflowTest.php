<?php

use FluxErp\Models\Resource;
use FluxErp\Models\ResourceBooking;

beforeEach(function (): void {
    $this->resource = Resource::factory()->create([
        'name' => 'Browser Test Resource',
        'is_active' => true,
    ]);

    $this->booking = ResourceBooking::factory()->create([
        'resource_id' => $this->resource->getKey(),
    ]);
});

test('resource list loads without js errors', function (): void {
    visit(route('resources.resources'))
        ->assertRoute('resources.resources')
        ->assertNoSmoke();
});

test('resource list shows data table', function (): void {
    visit(route('resources.resources'))
        ->assertRoute('resources.resources')
        ->assertNoSmoke()
        ->assertPresent('[tall-datatable]');
});

test('resource detail page loads', function (): void {
    visit(route('resources.id?', ['id' => $this->resource->getKey()]))
        ->assertNoSmoke();
});

test('resource detail shows resource name', function (): void {
    visit(route('resources.id?', ['id' => $this->resource->getKey()]))
        ->assertNoSmoke()
        ->assertSee('Browser Test Resource');
});

test('resource detail loads without js errors', function (): void {
    visit(route('resources.id?', ['id' => $this->resource->getKey()]))
        ->assertNoSmoke()
        ->assertNoJavascriptErrors();
});

test('calendar page loads with resource bookings', function (): void {
    visit(route('calendars'))
        ->assertNoSmoke()
        ->assertNoJavascriptErrors();
});
