<?php

namespace FluxErp\Actions\TicketType;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\CreateTicketTypeRequest;
use FluxErp\Models\TicketType;
use Illuminate\Support\Facades\Validator;

class CreateTicketType extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new CreateTicketTypeRequest())->rules();
    }

    public static function models(): array
    {
        return [TicketType::class];
    }

    public function performAction(): TicketType
    {
        $ticketType = new TicketType($this->data);
        $ticketType->save();

        return $ticketType;
    }

    public function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new TicketType());

        $this->data = $validator->validate();
    }
}
