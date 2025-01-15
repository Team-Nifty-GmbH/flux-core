<?php

namespace FluxErp\Actions\Tag;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Tag;
use FluxErp\Rulesets\Tag\UpdateTagRuleset;
use Illuminate\Validation\ValidationException;

class UpdateTag extends FluxAction
{
    protected function getRulesets(): string|array
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

    protected function validateData(): void
    {
        parent::validateData();

        if ($this->getData('name')
            && resolve_static(Tag::class, 'query')
                ->whereKeyNot($this->getData('id'))
                ->where('name', $this->getData('name'))
                ->where(
                    'type',
                    resolve_static(Tag::class, 'query')
                        ->whereKey($this->getData('id'))
                        ->value('type')
                )
                ->exists()
        ) {
            throw ValidationException::withMessages([
                'name' => [__('validation.already_exists', ['model' => __('Tag')])],
            ]);
        }
    }
}
