<?php

namespace FluxErp\Actions\Tag;

use FluxErp\Actions\DispatchableFluxAction;
use FluxErp\Contracts\SupportsBulkExecution;
use FluxErp\Models\Tag;
use FluxErp\Rulesets\Tag\DeleteTagRuleset;

class DeleteTag extends DispatchableFluxAction implements SupportsBulkExecution
{
    public static function models(): array
    {
        return [Tag::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteTagRuleset::class;
    }

    public function performAction(): mixed
    {
        return resolve_static(Tag::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
