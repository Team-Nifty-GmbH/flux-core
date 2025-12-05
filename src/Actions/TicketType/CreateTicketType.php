<?php

namespace FluxErp\Actions\TicketType;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\TicketType;
use FluxErp\Rulesets\TicketType\CreateTicketTypeRuleset;

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
        $ticketType = app(TicketType::class, ['attributes' => $this->getData()]);
        $ticketType->save();

        return $ticketType->refresh();
    }
}
