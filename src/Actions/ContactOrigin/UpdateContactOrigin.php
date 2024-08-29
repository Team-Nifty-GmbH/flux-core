<?php

namespace FluxErp\Actions\ContactOrigin;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\ContactOrigin;
use FluxErp\Rulesets\ContactOrigin\UpdateContactOriginRuleset;
use Illuminate\Database\Eloquent\Model;

class UpdateContactOrigin extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(UpdateContactOriginRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [ContactOrigin::class];
    }

    public function performAction(): Model
    {
        $contactOrigin = resolve_static(ContactOrigin::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $contactOrigin->fill($this->data);
        $contactOrigin->save();

        return $contactOrigin->withoutRelations()->fresh();
    }
}
