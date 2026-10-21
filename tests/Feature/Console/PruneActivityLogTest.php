<?php

use FluxErp\Console\Commands\PruneCommand;
use FluxErp\Models\Activity;
use Illuminate\Support\Facades\Artisan;

test('model:prune deletes activities older than the retention period', function (): void {
    config(['activitylog.clean_after_days' => 365]);

    $old = activity()->log('old');
    $old->forceFill(['created_at' => now()->subDays(366)])->saveQuietly();
    $recent = activity()->log('recent');
    $recent->forceFill(['created_at' => now()->subDays(364)])->saveQuietly();

    Artisan::call(PruneCommand::class);

    expect(Activity::query()->whereKey($old->getKey())->exists())->toBeFalse()
        ->and(Activity::query()->whereKey($recent->getKey())->exists())->toBeTrue();
});
