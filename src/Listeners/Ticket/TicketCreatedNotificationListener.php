<?php

namespace FluxErp\Listeners\Ticket;

use FluxErp\Listeners\NotificationEloquentEventSubscriber;
use FluxErp\Models\User;
use Illuminate\Database\Eloquent\Builder;

class TicketCreatedNotificationListener
{
    public function __construct()
    {
        //
    }

    public function handle(NotificationEloquentEventSubscriber $eloquentEventSubscriber): void
    {
        if (is_null($eloquentEventSubscriber->model->ticket_type_id)) {
            return;
        }

        $notificationRoles = $eloquentEventSubscriber->model
            ->ticketType
            ->roles()
            ->get()
            ->pluck('id')
            ->toArray();
        $users = User::query()
            ->whereHas('roles', function (Builder $query) use ($notificationRoles) {
                $query->whereIntegerInRaw('id', $notificationRoles);
            })
            ->get();

        $eloquentEventSubscriber->notifiables = $eloquentEventSubscriber->notifiables->merge($users);
    }
}
