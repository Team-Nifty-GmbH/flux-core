<?php

namespace FluxErp\Actions\Ticket;

use FluxErp\Actions\FluxAction;
use FluxErp\Events\Ticket\TicketAssignedEvent;
use FluxErp\Models\Ticket;
use FluxErp\Models\TicketType;
use FluxErp\Rulesets\Ticket\UpdateTicketRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class UpdateTicket extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return UpdateTicketRuleset::class;
    }

    public static function models(): array
    {
        return [Ticket::class];
    }

    public function performAction(): Model
    {
        $users = Arr::pull($this->data, 'users');

        $ticket = resolve_static(Ticket::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $ticket->fill($this->data);
        $ticket->save();

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

    protected function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(app(Ticket::class));

        $this->data = $validator->validate();
    }
}
