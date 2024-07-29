<?php

namespace FluxErp\Actions\Ticket;

use FluxErp\Actions\EventSubscription\CreateEventSubscription;
use FluxErp\Actions\FluxAction;
use FluxErp\Models\Comment;
use FluxErp\Models\Ticket;
use FluxErp\Models\TicketType;
use FluxErp\Models\User;
use FluxErp\Rulesets\Ticket\UpdateTicketRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class UpdateTicket extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(UpdateTicketRuleset::class, 'getRules');
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
            $sync = $ticket->users()->sync($users);

            foreach (data_get($sync, 'attached', []) as $user) {
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
                ])->execute();
            }
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
