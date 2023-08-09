<?php

namespace FluxErp\Actions\Ticket;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\UpdateTicketRequest;
use FluxErp\Models\Ticket;
use FluxErp\Models\TicketType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class UpdateTicket extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new UpdateTicketRequest())->rules();

        if ($this->data['ticket_type_id'] ?? false) {
            TicketType::query()->whereKey($this->data['ticket_type_id'])->first();

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

    public function performAction(): Model
    {
        $users = Arr::pull($this->data, 'users');

        $ticket = Ticket::query()
            ->whereKey($this->data['id'])
            ->first();

        $ticket->fill($this->data);
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
