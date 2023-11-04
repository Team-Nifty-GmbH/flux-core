<?php

namespace FluxErp\Actions\Ticket;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\CreateTicketRequest;
use FluxErp\Models\AdditionalColumn;
use FluxErp\Models\Client;
use FluxErp\Models\Ticket;
use FluxErp\Models\TicketType;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CreateTicket extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new CreateTicketRequest())->rules();

        if ($this->data['ticket_type_id'] ?? false) {
            $this->rules = array_merge(
                $this->rules,
                TicketType::query()
                    ->whereKey($this->data['ticket_type_id'])
                    ->first()
                    ?->hasAdditionalColumnsValidationRules() ?? []
            );
        }
    }

    public static function models(): array
    {
        return [Ticket::class];
    }

    public function performAction(): Ticket
    {
        $users = Arr::pull($this->data, 'users');

        $ticket = new Ticket($this->data);

        if ($ticket->ticket_type_id) {
            $meta = $ticket->getDirtyMeta();

            $additionalColumns = Arr::keyBy(
                AdditionalColumn::query()
                    ->where('model_type', TicketType::class)
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
            Auth::user()?->client_id ?? Client::query()->first()?->id
        );

        $ticket->save();

        if (is_array($users)) {
            $ticket->users()->sync($users);
        }

        return $ticket->refresh();
    }

    public function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new Ticket());

        $this->data = $validator->validate();
    }
}
