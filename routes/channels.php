<?php

use FluxErp\Models\Address;
use FluxErp\Models\Calendar;
use FluxErp\Models\CalendarEvent;
use FluxErp\Models\Contact;
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

Broadcast::channel(Address::getBroadcastChannelRoute(), function ($user) {
    return $user->can(channel_to_permission(Address::getBroadcastChannelRoute()));
});

Broadcast::channel(Calendar::getBroadcastChannelRoute(), function ($user) {
    return $user->can(channel_to_permission(Calendar::getBroadcastChannelRoute()));
});

Broadcast::channel(CalendarEvent::getBroadcastChannelRoute(), function ($user) {
    return $user->can(channel_to_permission(CalendarEvent::getBroadcastChannelRoute()));
});

Broadcast::channel(Contact::getBroadcastChannel(), function ($user) {
    return $user->can(channel_to_permission(Contact::getBroadcastChannel()));
});

Broadcast::channel(Contact::getBroadcastChannelRoute(), function ($user) {
    return $user->can(channel_to_permission(Contact::getBroadcastChannelRoute()));
});

Broadcast::channel(\FluxErp\Models\User::getBroadcastChannelRoute(), function ($user, $id) {
    return (int) $user->id === (int) $id;
});
