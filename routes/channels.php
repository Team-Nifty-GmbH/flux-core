<?php

use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use FluxErp\Models\Order;
use FluxErp\Models\Project;
use FluxErp\Models\ProjectTask;
use FluxErp\Models\Ticket;
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

Broadcast::channel('FluxErp.Models.Contact.{contact}', function ($user) {
    return $user->can(channel_to_permission((new Contact())->broadcastChannelRoute()));
});

Broadcast::channel('FluxErp.Models.Log.{log}', function ($user) {
    return $user->can(channel_to_permission((new \FluxErp\Models\Log())->broadcastChannelRoute()));
});

Broadcast::channel('FluxErp.Models.Log', function ($user) {
    return $user->can(channel_to_permission((new \FluxErp\Models\Log())->broadcastChannelRoute()));
});

Broadcast::channel('FluxErp.Models.Order.{order}', function ($user) {
    return $user->can(channel_to_permission((new Order())->broadcastChannelRoute()));
});

Broadcast::channel('FluxErp.Models.Order', function ($user) {
    return $user->can(channel_to_permission((new Order())->broadcastChannelRoute()));
});

Broadcast::channel('FluxErp.Models.Project.{project}', function ($user) {
    return $user->can(channel_to_permission((new Project())->broadcastChannelRoute()));
});

Broadcast::channel('FluxErp.Models.Project', function ($user) {
    return $user->can(channel_to_permission((new Project())->broadcastChannelRoute()));
});

Broadcast::channel('FluxErp.Models.ProjectTask.{projectTask}', function ($user) {
    return $user->can(channel_to_permission((new ProjectTask())->broadcastChannelRoute()));
});

Broadcast::channel('FluxErp.Models.ProjectTask', function ($user) {
    return $user->can(channel_to_permission((new ProjectTask())->broadcastChannelRoute()));
});

Broadcast::channel('FluxErp.Models.Ticket.{ticket}', function ($user) {
    return $user->can(channel_to_permission((new Ticket())->broadcastChannelRoute()));
});

Broadcast::channel('FluxErp.Models.Ticket', function ($user) {
    return $user->can(channel_to_permission((new Ticket())->broadcastChannelRoute()));
});

Broadcast::channel('FluxErp.Models.User.{user}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
