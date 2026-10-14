<?php

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

            $record = $class::query()->where(app($class)->getRouteKeyName(), $key)->first();

            if (is_null($record) || ! is_a($user, morphed_model('address'))) {
                return ! is_null($record);
            }

            $attributes = $record->getAttributes();
            $contactId = match (true) {
                is_a($record, morphed_model('contact')) => $record->getKey(),
                array_key_exists('contact_id', $attributes) => data_get($attributes, 'contact_id'),
                data_get($attributes, 'authenticatable_type') === morph_alias(morphed_model('address')) => resolve_static(
                    morphed_model('address'),
                    'query'
                )
                    ->whereKey(data_get($attributes, 'authenticatable_id'))
                    ->value('contact_id'),
                default => null,
            };

            return ! is_null($contactId) && (int) $contactId === (int) $user->contact_id;
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

Broadcast::channel('action.*', fn () => true);

Broadcast::channel(
    'presence',
    fn (User $user): array => ['id' => $user->getKey()],
    ['guards' => ['web', 'sanctum']]
);
