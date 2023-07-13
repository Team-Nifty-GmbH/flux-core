<?php

namespace FluxErp\Actions\Media;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\UpdateMediaRequest;
use FluxErp\Models\Media;
use Illuminate\Database\Eloquent\Model;

class UpdateMedia extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new UpdateMediaRequest())->rules();
    }

    public static function models(): array
    {
        return [Media::class];
    }

    public function execute(): Model
    {
        $media = Media::query()
            ->whereKey($this->data['id'])
            ->first();

        $media->fill($this->data);
        $media->save();

        return $media->withoutRelations();
    }
}
