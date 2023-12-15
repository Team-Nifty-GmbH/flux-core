<?php

namespace FluxErp\Actions\Tag;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Tag;

class DeleteTag extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:tags,id',
        ];
    }

    public static function models(): array
    {
        return [Tag::class];
    }

    public function performAction(): mixed
    {
        return Tag::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
