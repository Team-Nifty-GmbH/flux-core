<?php

namespace FluxErp\Actions\FormBuilderResponse;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\FormBuilderResponse;
use FluxErp\Rulesets\FormBuilderResponse\CreateFormBuilderResponseRuleset;

class CreateFormBuilderResponse extends FluxAction
{
    public static function models(): array
    {
        return [FormBuilderResponse::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateFormBuilderResponseRuleset::class;
    }

    public function performAction(): FormBuilderResponse
    {
        $formBuilderResponse = app(FormBuilderResponse::class, ['attributes' => $this->data]);
        $formBuilderResponse->save();

        return $formBuilderResponse->refresh();
    }
}
