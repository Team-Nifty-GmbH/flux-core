<?php

namespace FluxErp\Actions\TicketType;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\TicketType;
use FluxErp\Rulesets\TicketType\CreateTicketTypeRuleset;
use Illuminate\Support\Arr;

class CreateTicketType extends FluxAction
{
    public static function models(): array
    {
        return [TicketType::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateTicketTypeRuleset::class;
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
}
