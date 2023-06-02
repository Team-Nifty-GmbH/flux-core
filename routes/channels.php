<?php

use FluxErp\Models\Address;
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

Broadcast::channel('FluxErp.Models.Address.{address}', function ($user) {
    return $user->can(channel_to_permission(Address::getBroadcastChannelRoute()));
});

Broadcast::channel('FluxErp.Models.Contact.{contact}', function ($user) {
    return $user->can(channel_to_permission(Contact::getBroadcastChannelRoute()));
});

Broadcast::channel('FluxErp.Models.User.{user}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('FluxErp.Models.Log.{Log}', function ($user) {
    return $user->can(channel_to_permission((new \FluxErp\Models\Log())->broadcastChannelRoute()));
});

Broadcast::channel('FluxErp.Models.Log', function ($user) {
    return $user->can(channel_to_permission((new \FluxErp\Models\Log())->broadcastChannelRoute()));
});
