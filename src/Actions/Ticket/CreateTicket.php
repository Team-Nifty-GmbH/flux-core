<?php

namespace FluxErp\Actions\Ticket;

use FluxErp\Actions\FluxAction;
use FluxErp\Events\Ticket\TicketAssignedEvent;
use FluxErp\Models\AdditionalColumn;
use FluxErp\Models\Client;
use FluxErp\Models\Ticket;
use FluxErp\Models\TicketType;
use FluxErp\Models\User;
use FluxErp\Rulesets\Ticket\CreateTicketRuleset;
use FluxErp\Traits\Notifiable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class CreateTicket extends FluxAction
{
    public static function models(): array
    {
        return [Ticket::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateTicketRuleset::class;
    }

    public function performAction(): Ticket
    {
        $users = Arr::pull($this->data, 'users');

        $ticket = app(Ticket::class, ['attributes' => $this->data]);

        if ($ticket->ticket_type_id) {
            $meta = $ticket->getDirtyMeta();

            $additionalColumns = Arr::keyBy(
                resolve_static(AdditionalColumn::class, 'query')
                    ->where('model_type', app(TicketType::class)->getMorphClass())
                    ->where('model_id', $ticket->ticket_type_id)
                    ->select(['id', 'name'])
                    ->get()
                    ->toArray(),
                'name'
            );

            foreach ($meta as $key => $item) {
                if (array_key_exists($key, $additionalColumns)) {
                    $item->forceType($ticket->ticketType->getCastForMetaKey($key))
                        ->forceFill([
                            'additional_column_id' => $additionalColumns[$key]['id'],
                        ]);

                    $ticket->setMetaChanges($meta->put($key, $item));
                }
            }
        }

        $ticket->getSerialNumber(
            'ticket_number',
            Auth::user()?->client_id ?? resolve_static(Client::class, 'query')->where('is_active', true)->first()?->id
        );

        $ticket->save();

        if (in_array(Notifiable::class, class_uses_recursive($ticket->authenticatable))) {
            $ticket->authenticatable->subscribeNotificationChannel($ticket->broadcastChannel());
        }

        if (is_array($users)) {
            if ($ticket->authenticatable->getMorphClass() === morph_alias(User::class)) {
                $users = array_filter($users, fn (int $user) => $user !== $ticket->authenticatable_id);
            }

            $ticket->users()->attach($users);
            event(TicketAssignedEvent::make($ticket)
                ->subscribeChannel(collect($users))
                ->subscribeChannel(
                    [$ticket->authenticatable_id],
                    morphed_model($ticket->authenticatable->getMorphClass())
                )
            );
        }

        return $ticket->refresh();
    }

    protected function prepareForValidation(): void
    {
        if ($ticketTypeId = $this->getData('ticket_type_id')) {
            $this->rules = array_merge(
                $this->rules,
                resolve_static(TicketType::class, 'query')
                    ->whereKey($ticketTypeId)
                    ->first()
                    ?->hasAdditionalColumnsValidationRules() ?? []
            );
        }
    }
}
