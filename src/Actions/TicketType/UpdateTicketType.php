<?php

namespace FluxErp\Actions\TicketType;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\TicketType;
use FluxErp\Rulesets\TicketType\UpdateTicketTypeRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class UpdateTicketType extends FluxAction
{
    public static function getRulesets(): string|array
    {
        return UpdateTicketTypeRuleset::class;
    }

    public static function models(): array
    {
        return [TicketType::class];
    }

    public function performAction(): Model
    {
        $ticketType = resolve_static(TicketType::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $roles = Arr::pull($this->data, 'roles');

        $ticketType->fill($this->data);
        $ticketType->save();

        if (! is_null($roles)) {
            $ticketType->roles()->sync($roles);
        }

        return $ticketType->withoutRelations()->fresh();
    }

    protected function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(app(TicketType::class));

        $this->data = $validator->validate();
    }
}
