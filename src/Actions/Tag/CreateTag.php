<?php

namespace FluxErp\Actions\Tag;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\CreateTagRequest;
use FluxErp\Models\Tag;

class CreateTag extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new CreateTagRequest())->rules();
    }

    public static function models(): array
    {
        return [Tag::class];
    }

    public function performAction(): Tag
    {
        $stockPosting = new Tag($this->data);
        $stockPosting->save();

        return $stockPosting->fresh();
    }
}
