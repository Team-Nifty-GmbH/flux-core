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

Broadcast::channel('FluxErp.Models.Contact', function ($user) {
    return $user->can(channel_to_permission((new Contact())->broadcastChannelRoute()));
});

Broadcast::channel('FluxErp.Models.Contact.{contact}', function ($user) {
    return $user->can(channel_to_permission((new Contact())->broadcastChannelRoute()));
});

Broadcast::channel('FluxErp.Models.Log.{log}', function ($user) {
    return $user->can(channel_to_permission((new Log())->broadcastChannelRoute()));
});

Broadcast::channel('FluxErp.Models.Log', function ($user) {
    return $user->can(channel_to_permission((new Log())->broadcastChannelRoute()));
});

Broadcast::channel('FluxErp.Models.Communication.{message}', function ($user) {
    return $user->can(channel_to_permission((new Communication())->broadcastChannelRoute()));
});

Broadcast::channel('FluxErp.Models.Communication', function ($user) {
    return $user->can(channel_to_permission((new Communication())->broadcastChannelRoute()));
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

Broadcast::channel('FluxErp.Models.Schedule.{schedule}', function ($user) {
    return $user->can(channel_to_permission((new Schedule())->broadcastChannelRoute()));
});

Broadcast::channel('FluxErp.Models.Schedule', function ($user) {
    return $user->can(channel_to_permission((new Schedule())->broadcastChannelRoute()));
});

Broadcast::channel('FluxErp.Models.Task.{task}', function ($user) {
    return $user->can(channel_to_permission((new Task())->broadcastChannelRoute()));
});

Broadcast::channel('FluxErp.Models.Task', function ($user) {
    return $user->can(channel_to_permission((new Task())->broadcastChannelRoute()));
});

Broadcast::channel('FluxErp.Models.Ticket.{ticket}', function ($user) {
    return $user->can(channel_to_permission((new Ticket())->broadcastChannelRoute()));
});

Broadcast::channel('FluxErp.Models.Ticket', function ($user) {
    return $user->can(channel_to_permission((new Ticket())->broadcastChannelRoute()));
});

Broadcast::channel('FluxErp.Models.Transaction.{transaction}', function ($user) {
    return $user->can(channel_to_permission((new Transaction())->broadcastChannelRoute()));
});

Broadcast::channel('FluxErp.Models.Transaction', function ($user) {
    return $user->can(channel_to_permission((new Transaction())->broadcastChannelRoute()));
});

Broadcast::channel('FluxErp.Models.WorkTime.{workTime}', function ($user) {
    return $user->can(channel_to_permission((new WorkTime())->broadcastChannelRoute()));
});

Broadcast::channel('FluxErp.Models.WorkTime', function ($user) {
    return $user->can(channel_to_permission((new WorkTime())->broadcastChannelRoute()));
});

Broadcast::channel('FluxErp.Models.User.{user}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('job-batch.{id}', function () {
    return true;
});
