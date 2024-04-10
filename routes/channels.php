<?php

use FluxErp\Models\Address;
use FluxErp\Models\Communication;
use FluxErp\Models\Contact;
use FluxErp\Models\Log;
use FluxErp\Models\Order;
use FluxErp\Models\Project;
use FluxErp\Models\Schedule;
use FluxErp\Models\Task;
use FluxErp\Models\Ticket;
use FluxErp\Models\Transaction;
use FluxErp\Models\WorkTime;
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

foreach (Relation::morphMap() as $alias => $class) {
    $class = resolve_static($class, 'class');
    $channel = class_to_broadcast_channel($class);

    Broadcast::channel($channel, function ($user) use ($channel) {
        return $user->can(channel_to_permission($channel));
    });

    $channel = class_to_broadcast_channel($class, false);
    Broadcast::channel($channel, function ($user) use ($channel) {
        return $user->can(channel_to_permission($channel));
    });
}

Broadcast::channel('FluxErp.Models.User.{user}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('job-batch.{id}', function () {
    return true;
});
