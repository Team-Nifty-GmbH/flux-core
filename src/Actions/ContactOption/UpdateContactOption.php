<?php

namespace FluxErp\Actions\ContactOption;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\ContactOption;
use FluxErp\Rulesets\ContactOption\UpdateContactOptionRuleset;
use Illuminate\Database\Eloquent\Model;

class UpdateContactOption extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(UpdateContactOptionRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [ContactOption::class];
    }

    public function performAction(): Model
    {
        $contactOption = app(ContactOption::class)->query()
            ->whereKey($this->data['id'])
            ->first();

        $contactOption->fill($this->data);
        $contactOption->save();

        return $contactOption->withoutRelations()->fresh();
    }
}
