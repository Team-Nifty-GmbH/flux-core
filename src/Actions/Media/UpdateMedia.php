<?php

namespace FluxErp\Actions\Media;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Media;
use FluxErp\Rulesets\Media\UpdateMediaRuleset;
use Illuminate\Database\Eloquent\Model;

class UpdateMedia extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(UpdateMediaRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [Media::class];
    }

    public function performAction(): Model
    {
        $media = resolve_static(Media::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $media->fill($this->data);
        $media->save();

        return $media->withoutRelations();
    }
}
