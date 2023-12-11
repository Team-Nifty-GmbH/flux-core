<?php

namespace FluxErp\Actions\Tag;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\UpdateTagRequest;
use FluxErp\Models\Tag;

class UpdateTag extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new UpdateTagRequest())->rules();
    }

    public static function models(): array
    {
        return [Tag::class];
    }

    public function performAction(): Tag
    {
        $tag = Tag::query()
            ->whereKey($this->data['id'])
            ->first();

        $tag->fill($this->data);
        $tag->save();

        return $tag->withoutRelations()->fresh();
    }
}
