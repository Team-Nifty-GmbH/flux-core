<?php

namespace FluxErp\Listeners\Ticket;

use FluxErp\Listeners\NotificationEloquentEventSubscriber;
use FluxErp\Models\User;
use Illuminate\Database\Query\JoinClause;

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

        $users = User::query()
            ->join('model_has_roles', function (JoinClause $join) {
                $join->on('users.id', '=', 'model_has_roles.model_id')
                    ->where('model_has_roles.model_type', User::class);
            })
            ->join('role_ticket_type AS rtt', 'model_has_roles.role_id', '=', 'rtt.role_id')
            ->where('rtt.ticket_type_id', $eloquentEventSubscriber->model->ticket_type_id)
            ->get();

        $eloquentEventSubscriber->notifiables = $eloquentEventSubscriber->notifiables->merge($users);
    }
}
