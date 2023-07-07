<?php

namespace FluxErp\Actions\TicketType;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\UpdateTicketTypeRequest;
use FluxErp\Models\TicketType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class UpdateTicketType extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new UpdateTicketTypeRequest())->rules();
    }

    public static function models(): array
    {
        return [TicketType::class];
    }

    public function execute(): Model
    {
        $ticketType = TicketType::query()
            ->whereKey($this->data['id'])
            ->first();

        $ticketType->fill($this->data);
        $ticketType->save();

        return $ticketType->withoutRelations()->fresh();
    }

    public function validate(): static
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new TicketType());

        $this->data = $validator->validate();

        return $this;
    }
}
