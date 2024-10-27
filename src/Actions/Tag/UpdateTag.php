<?php

namespace FluxErp\Actions\Tag;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Tag;
use FluxErp\Rulesets\Tag\UpdateTagRuleset;

class UpdateTag extends FluxAction
{
    public static function getRulesets(): string|array
    {
        return UpdateTagRuleset::class;
    }

    public static function models(): array
    {
        return [Tag::class];
    }

    public function performAction(): Tag
    {
        $tag = resolve_static(Tag::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $tag->fill($this->data);
        $tag->save();

        return $tag->withoutRelations()->fresh();
    }
}
