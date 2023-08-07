<?php

namespace FluxErp\Actions\TicketType;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\UpdateTicketTypeRequest;
use FluxErp\Models\TicketType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class UpdateTicketType extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new UpdateTicketTypeRequest())->rules();
    }

    public static function models(): array
    {
        return [TicketType::class];
    }

    public function performAction(): Model
    {
        $ticketType = TicketType::query()
            ->whereKey($this->data['id'])
            ->first();

        $ticketType->fill($this->data);
        $ticketType->save();

        return $ticketType->withoutRelations()->fresh();
    }

    public function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new TicketType());

        $this->data = $validator->validate();
    }
}
