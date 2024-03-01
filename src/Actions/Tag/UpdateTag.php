<?php

namespace FluxErp\Actions\Tag;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Tag;
use FluxErp\Rulesets\Tag\UpdateTagRuleset;

class UpdateTag extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(UpdateTagRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [Tag::class];
    }

    public function performAction(): Tag
    {
        $tag = app(Tag::class)->query()
            ->whereKey($this->data['id'])
            ->first();

        $tag->fill($this->data);
        $tag->save();

        return $tag->withoutRelations()->fresh();
    }
}
