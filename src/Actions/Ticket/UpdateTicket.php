<?php

namespace FluxErp\Actions\Ticket;

use FluxErp\Actions\FluxAction;
use FluxErp\Events\Ticket\TicketAssignedEvent;
use FluxErp\Models\Ticket;
use FluxErp\Models\TicketType;
use FluxErp\Rulesets\Ticket\UpdateTicketRuleset;
use FluxErp\Traits\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class UpdateTicket extends FluxAction
{
    public static function models(): array
    {
        return [Ticket::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateTicketRuleset::class;
    }

    public function performAction(): Model
    {
        $users = Arr::pull($this->data, 'users');

        $ticket = resolve_static(Ticket::class, 'query')
            ->whereKey($this->data['id'])
            ->first();
        $authenticatable = $ticket->authenticatable;

        $ticket->fill($this->data);
        $ticket->save();
        $ticket->load('authenticatable');

        if (
            $ticket->authenticatable->isNot($authenticatable)
            && in_array(Notifiable::class, class_uses_recursive($ticket->authenticatable))
        ) {
            $authenticatable?->unsubscribeNotificationChannel($ticket->broadcastChannel());
            $ticket->authenticatable->subscribeNotificationChannel($ticket->broadcastChannel());
        }

        if (is_array($users)) {
            $result = $ticket->users()->sync($users);

            event(TicketAssignedEvent::make($ticket)
                ->subscribeChannel(collect(data_get($result, 'attached')))
                ->unsubscribeChannel(collect(data_get($result, 'detached')))
            );
        }

        return $ticket->refresh();
    }

    protected function prepareForValidation(): void
    {
        if ($this->data['ticket_type_id'] ?? false) {
            $this->rules = array_merge(
                $this->rules,
                resolve_static(TicketType::class, 'query')
                    ->whereKey($this->data['ticket_type_id'])
                    ->first()
                    ?->hasAdditionalColumnsValidationRules() ?? []
            );
        }
    }
}
