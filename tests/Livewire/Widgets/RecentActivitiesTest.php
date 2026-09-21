<?php

use FluxErp\Livewire\Widgets\RecentActivities;
use FluxErp\Models\Activity;
use FluxErp\Models\OrderType;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;

test('renders successfully', function (): void {
    Livewire::test(RecentActivities::class)
        ->assertOk();
});

test('activity log is indexed on created_at', function (): void {
    expect(Schema::hasIndex('activity_log', ['created_at']))->toBeTrue();
});

test('the list query selects neither the json columns nor everything', function (): void {
    OrderType::factory()->create();

    $queries = [];
    DB::listen(function (QueryExecuted $query) use (&$queries): void {
        $queries[] = $query->sql;
    });

    Livewire::test(RecentActivities::class)
        ->call('calculateList');

    $listQuery = Arr::first(
        $queries,
        fn (string $sql) => str_contains($sql, 'from `activity_log`') && str_contains($sql, 'order by')
    );

    expect($listQuery)->not->toBeNull()
        ->and($listQuery)->toStartWith(
            'select `id`, `description`, `subject_type`, `subject_id`, '
            . '`causer_type`, `causer_id`, `created_at` from `activity_log`'
        )
        ->and($listQuery)->not->toContain('properties')
        ->and($listQuery)->not->toContain('attribute_changes');
});

test('the narrowed select still resolves causer, subject and timestamp', function (): void {
    $orderType = OrderType::factory()->create();

    $items = Livewire::test(RecentActivities::class)
        ->call('calculateList')
        ->get('items');

    expect($items)->not->toBeEmpty()
        ->and($items[0]['label'])->toContain($this->user->name)
        ->and($items[0]['subLabel'])->toBe($orderType->getLabel())
        ->and($items[0]['value'])->toBe(
            $orderType->created_at
                ->locale(app()->getLocale())
                ->timezone($this->user->timezone ?? config('app.timezone'))
                ->isoFormat('L LT')
        );
});

test('load more asks whether another row exists instead of counting the table', function (): void {
    Activity::query()->delete();
    OrderType::factory()->count(12)->create();

    $queries = [];
    DB::listen(function (QueryExecuted $query) use (&$queries): void {
        $queries[] = $query->sql;
    });

    Livewire::test(RecentActivities::class)
        ->call('hasMore')
        ->assertReturned(true);

    expect(Arr::first($queries, fn (string $sql) => str_contains($sql, 'count(')))->toBeNull();
});

test('load more stops once no further row is left', function (): void {
    Activity::query()->delete();
    OrderType::factory()->count(2)->create();

    Livewire::test(RecentActivities::class)
        ->call('hasMore')
        ->assertReturned(false);
});
