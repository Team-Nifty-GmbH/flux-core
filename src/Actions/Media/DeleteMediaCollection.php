<?php

namespace FluxErp\Actions\Media;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\DeleteMediaCollectionRequest;
use FluxErp\Models\Media;

class DeleteMediaCollection extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new DeleteMediaCollectionRequest())->rules();
    }

    public static function models(): array
    {
        return [Media::class];
    }

    public function performAction(): ?bool
    {
        return Media::query()
            ->where('model_type', $this->data['model_type'])
            ->where('model_id', $this->data['model_id'])
            ->where('collection_name', 'LIKE', $this->data['collection_name'] . '.%')
            ->orWhere('collection_name', $this->data['collection_name'])
            ->delete();
    }
}
