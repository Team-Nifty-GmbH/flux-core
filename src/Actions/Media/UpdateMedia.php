<?php

namespace FluxErp\Actions\Media;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\UpdateMediaRequest;
use FluxErp\Models\Media;
use Illuminate\Database\Eloquent\Model;

class UpdateMedia extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new UpdateMediaRequest())->rules();
    }

    public static function models(): array
    {
        return [Media::class];
    }

    public function performAction(): Model
    {
        $media = Media::query()
            ->whereKey($this->data['id'])
            ->first();

        $media->fill($this->data);
        $media->save();

        return $media->withoutRelations();
    }
}
