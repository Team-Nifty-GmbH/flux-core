<?php

namespace FluxErp\Actions\Tag;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Tag;
use FluxErp\Rulesets\Tag\DeleteTagRuleset;

class DeleteTag extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return DeleteTagRuleset::class;
    }

    public static function models(): array
    {
        return [Tag::class];
    }

    public function performAction(): mixed
    {
        return resolve_static(Tag::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
