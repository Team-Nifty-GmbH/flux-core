<?php

use FluxErp\Console\Commands\PruneCommand;
use FluxErp\Models\Activity;
use FluxErp\Settings\ActivityLogSettings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;

function logActivityDaysAgo(?string $event, int $days): Model
{
    $activity = activity()->log($event ?? 'manual');
    $activity->forceFill(['event' => $event, 'created_at' => now()->subDays($days)])->saveQuietly();

    return $activity;
}

test('model:prune deletes activities older than the retention period of their event', function (): void {
    ActivityLogSettings::fake([
        'created_retention_days' => null,
        'updated_retention_days' => 365,
        'deleted_retention_days' => 730,
        'other_retention_days' => 30,
    ]);

    $kept = collect([
        logActivityDaysAgo('created', 5000),
        logActivityDaysAgo('updated', 364),
        logActivityDaysAgo('deleted', 366),
        logActivityDaysAgo(null, 29),
    ])->map->getKey();
    $pruned = collect([
        logActivityDaysAgo('updated', 366),
        logActivityDaysAgo('deleted', 731),
        logActivityDaysAgo(null, 31),
    ])->map->getKey();

    Artisan::call(PruneCommand::class);

    expect(Activity::query()->whereKey($kept)->count())->toBe(4)
        ->and(Activity::query()->whereKey($pruned)->exists())->toBeFalse();
});

test('model:prune keeps every activity when no retention period is set', function (): void {
    ActivityLogSettings::fake([
        'created_retention_days' => null,
        'updated_retention_days' => null,
        'deleted_retention_days' => null,
        'other_retention_days' => null,
    ]);

    $activities = collect([
        logActivityDaysAgo('created', 5000),
        logActivityDaysAgo('updated', 5000),
        logActivityDaysAgo(null, 5000),
    ])->map->getKey();

    Artisan::call(PruneCommand::class);

    expect(Activity::query()->whereKey($activities)->count())->toBe(3);
});
