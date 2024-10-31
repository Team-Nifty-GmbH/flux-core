<?php

namespace FluxErp\Actions\TicketType;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\TicketType;
use FluxErp\Rulesets\TicketType\CreateTicketTypeRuleset;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class CreateTicketType extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return CreateTicketTypeRuleset::class;
    }

    public static function models(): array
    {
        return [TicketType::class];
    }

    public function performAction(): TicketType
    {
        $roles = Arr::pull($this->data, 'roles');

        $ticketType = app(TicketType::class, ['attributes' => $this->data]);
        $ticketType->save();

        if ($roles) {
            $ticketType->roles()->sync($roles);
        }

        return $ticketType->fresh();
    }

    protected function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(app(TicketType::class));

        $this->data = $validator->validate();
    }
}
