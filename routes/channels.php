<?php

use Illuminate\Database\Eloquent\BroadcastsEvents;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Broadcast;

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

    $channel = class_to_broadcast_channel($class);

    Broadcast::channel($channel, function ($user) use ($channel) {
        return $user->can(channel_to_permission($channel));
    });

    $channel = class_to_broadcast_channel($class, false);
    Broadcast::channel($channel, function ($user) use ($channel) {
        return $user->can(channel_to_permission($channel));
    });
}

Broadcast::channel(
    class_to_broadcast_channel(morphed_model('user')),
    function ($user, $id) {
        return (int) $user->id === (int) $id;
    }
);

Broadcast::channel('job-batch.{id}', function () {
    return true;
});
