<?php

namespace FluxErp\Actions\Tag;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Tag;
use FluxErp\Rulesets\Tag\CreateTagRuleset;

class CreateTag extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return CreateTagRuleset::class;
    }

    public static function models(): array
    {
        return [Tag::class];
    }

    public function performAction(): Tag
    {
        $tag = app(Tag::class, ['attributes' => $this->data]);
        $tag->save();

        return $tag->fresh();
    }
}
