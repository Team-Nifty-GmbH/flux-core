<?php

namespace FluxErp\Actions\Resource;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Resource;
use FluxErp\Rulesets\Resource\CreateResourceRuleset;

class CreateResource extends FluxAction
{
    public static function models(): array
    {
        return [Resource::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateResourceRuleset::class;
    }

    public function performAction(): Resource
    {
        $resource = app(Resource::class, ['attributes' => $this->data]);
        $resource->save();

        return $resource->fresh();
    }
}
