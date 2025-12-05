<?php

namespace FluxErp\Actions\TicketType;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\TicketType;
use FluxErp\Rulesets\TicketType\UpdateTicketTypeRuleset;
use Illuminate\Database\Eloquent\Model;

class UpdateTicketType extends FluxAction
{
    public static function models(): array
    {
        return [TicketType::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateTicketTypeRuleset::class;
    }

    public function performAction(): Model
    {
        $ticketType = resolve_static(TicketType::class, 'query')
            ->whereKey($this->getData('id'))
            ->first();

        $ticketType->fill($this->getData());
        $ticketType->save();

        return $ticketType->withoutRelations()->refresh();
    }
}
