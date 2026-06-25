<?php

namespace FluxErp\Actions\Resource;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Resource;
use FluxErp\Rulesets\Resource\UpdateResourceRuleset;
use Illuminate\Database\Eloquent\Model;

class UpdateResource extends FluxAction
{
    public static function models(): array
    {
        return [Resource::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateResourceRuleset::class;
    }

    public function performAction(): Model
    {
        $resource = resolve_static(Resource::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $resource->fill($this->data);
        $resource->save();

        return $resource->withoutRelations()->fresh();
    }
}
