<?php

namespace FluxErp\Actions\Tag;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Tag;
use FluxErp\Rulesets\Tag\CreateTagRuleset;

class CreateTag extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(CreateTagRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [Tag::class];
    }

    public function performAction(): Tag
    {
        $stockPosting = app(Tag::class, ['attributes' => $this->data]);
        $stockPosting->save();

        return $stockPosting->fresh();
    }
}
