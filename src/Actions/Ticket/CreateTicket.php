<?php

namespace FluxErp\Actions\Ticket;

use FluxErp\Actions\EventSubscription\CreateEventSubscription;
use FluxErp\Actions\FluxAction;
use FluxErp\Models\AdditionalColumn;
use FluxErp\Models\Client;
use FluxErp\Models\Comment;
use FluxErp\Models\Ticket;
use FluxErp\Models\TicketType;
use FluxErp\Models\User;
use FluxErp\Rulesets\Ticket\CreateTicketRuleset;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CreateTicket extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return CreateTicketRuleset::class;
    }

    public static function models(): array
    {
        return [Ticket::class];
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

        if (is_array($users)) {
            if ($ticket->authenticatable->getMorphClass() === morph_alias(User::class)) {
                $users = array_filter($users, fn (int $user) => $user !== $ticket->authenticatable_id);
            }

            foreach (data_get($ticket->users()->sync($users), 'attached', []) as $user) {
                CreateEventSubscription::make([
                    'event' => eloquent_model_event(
                        'created',
                        resolve_static(Comment::class, 'class')
                    ),
                    'subscribable_id' => $user,
                    'subscribable_type' => morph_alias(User::class),
                    'model_type' => $ticket->getMorphClass(),
                    'model_id' => $ticket->id,
                    'is_broadcast' => false,
                    'is_notifiable' => true,
                ])->validate()->execute();
            }
        }

        CreateEventSubscription::make([
            'event' => eloquent_model_event(
                'created',
                resolve_static(Comment::class, 'class')
            ),
            'subscribable_id' => $ticket->authenticatable_id,
            'subscribable_type' => $ticket->authenticatable->getMorphClass(),
            'model_type' => $ticket->getMorphClass(),
            'model_id' => $ticket->id,
            'is_broadcast' => false,
            'is_notifiable' => true,
        ])->validate()->execute();

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

    protected function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(app(Ticket::class));

        $this->data = $validator->validate();
    }
}
