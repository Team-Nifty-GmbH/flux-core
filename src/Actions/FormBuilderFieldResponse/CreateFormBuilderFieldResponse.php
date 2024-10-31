<?php

namespace FluxErp\Actions\FormBuilderFieldResponse;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\FormBuilderFieldResponse;
use FluxErp\Rulesets\FormBuilderFieldResponse\CreateFormBuilderFieldResponseRuleset;

class CreateFormBuilderFieldResponse extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return CreateFormBuilderFieldResponseRuleset::class;
    }

    public static function models(): array
    {
        return [FormBuilderFieldResponse::class];
    }

    public function performAction(): FormBuilderFieldResponse
    {
        $formBuilderFieldResponse = app(FormBuilderFieldResponse::class, ['attributes' => $this->data]);
        $formBuilderFieldResponse->save();

        return $formBuilderFieldResponse->refresh();
    }
}
