<?php

use FluxErp\Actions\ActionManager;
use FluxErp\Http\Controllers\BroadcastingBatchAuthController;
use FluxErp\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\BroadcastsEvents;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

Route::post('/broadcasting/auth/batch', BroadcastingBatchAuthController::class)
    ->middleware(['web', 'auth:web'])
    ->name('broadcasting.auth.batch');

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

foreach (Relation::morphMap() as $class) {
    $class = resolve_static($class, 'class');
    if (! in_array(BroadcastsEvents::class, class_uses_recursive($class))) {
        continue;
    }

    // User has a dedicated ownership-scoped channel below; skip the generic one.
    if (is_a($class, morphed_model('user'), true)) {
        continue;
    }

    $channel = class_to_broadcast_channel($class);

    Broadcast::channel(
        $channel,
        function (Authenticatable $user, int|string $key) use ($class) {
            auth()->setUser($user);

            return $class::query()->where(app($class)->getRouteKeyName(), $key)->exists();
        },
        ['guards' => ['web', 'address']]
    );

    $channel = class_to_broadcast_channel($class, false);
    Broadcast::channel($channel . '.', fn () => true, ['guards' => ['web', 'token']]);
}

Broadcast::channel(
    class_to_broadcast_channel(morphed_model('user'), false) . '.{id}',
    fn (Authenticatable $user, int|string $id): bool => is_a($user, morphed_model('user'))
        && (int) $user->getKey() === (int) $id,
    ['guards' => ['web', 'address', 'sanctum', 'token']]
);

Broadcast::channel(
    'job-batch.{id}',
    fn () => true,
    ['guards' => ['web', 'sanctum']]
);

Broadcast::channel(
    'action.{action}',
    function (Authenticatable $user, string $action): bool {
        $class = data_get(app(ActionManager::class)->get(str_replace('-', '.', $action)), 'class');

        if (! $class) {
            return false;
        }

        auth()->shouldUse('web');
        auth()->setUser($user);

        return ! resolve_static($class, 'hasPermission')
            || $user->can('action.' . resolve_static($class, 'name'));
    },
    ['guards' => ['web', 'sanctum']]
);

Broadcast::channel(
    'presence',
    fn (User $user): array => ['id' => $user->getKey()],
    ['guards' => ['web', 'sanctum']]
);
