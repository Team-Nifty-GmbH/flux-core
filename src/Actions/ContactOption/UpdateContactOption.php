<?php

namespace FluxErp\Actions\ContactOption;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\ContactOption;
use FluxErp\Rulesets\ContactOption\UpdateContactOptionRuleset;
use Illuminate\Database\Eloquent\Model;

class UpdateContactOption extends FluxAction
{
    public static function getRulesets(): string|array
    {
        return UpdateContactOptionRuleset::class;
    }

    public static function models(): array
    {
        return [ContactOption::class];
    }

    public function performAction(): Model
    {
        $contactOption = resolve_static(ContactOption::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $contactOption->fill($this->data);
        $contactOption->save();

        return $contactOption->withoutRelations()->fresh();
    }
}
