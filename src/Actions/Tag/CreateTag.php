<?php

namespace FluxErp\Actions\Tag;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Tag;
use FluxErp\Rulesets\Tag\CreateTagRuleset;
use Illuminate\Validation\ValidationException;

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

    protected function validateData(): void
    {
        parent::validateData();

        if (resolve_static(Tag::class, 'query')
            ->where('name', $this->getData('name'))
            ->where('type', $this->getData('type'))
            ->exists()
        ) {
            throw ValidationException::withMessages([
                'name' => [__('validation.already_exists', ['model' => __('Tag')])],
            ]);
        }
    }
}
