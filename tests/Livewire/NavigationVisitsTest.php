<?php

use FluxErp\Livewire\Navigation;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->actingAs($this->user, 'web');

    foreach (['orders.orders', 'orders.orders', 'contacts.contacts'] as $description) {
        activity()
            ->causedBy($this->user)
            ->event('visit')
            ->log($description);
    }
});

test('the most visited pages are read once and then served from the cache', function (): void {
    $reads = 0;
    DB::listen(function (QueryExecuted $query) use (&$reads): void {
        if (str_contains($query->sql, 'activity_log') && str_contains($query->sql, 'group by')) {
            $reads++;
        }
    });

    Livewire::test(Navigation::class)->assertOk();
    Livewire::test(Navigation::class)->assertOk();

    expect(1)->toBe($reads)
        ->and(Cache::get('navigation.visits.' . $this->user->getMorphClass() . '.' . $this->user->getKey()))
        ->toBe(['orders.orders', 'contacts.contacts']);
});

test('activity log lookups by causer and event are backed by an index', function (): void {
    $columns = collect(Schema::getIndexes('activity_log'))
        ->firstWhere('name', 'activity_log_causer_event_index')['columns'] ?? null;

    expect(['causer_type', 'causer_id', 'event'])->toBe($columns);
});
