<?php

namespace FluxErp\Actions\FormBuilderFieldResponse;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\FormBuilderFieldResponse;
use FluxErp\Rulesets\FormBuilderFieldResponse\UpdateFormBuilderFieldResponseRuleset;

class UpdateFormBuilderFieldResponse extends FluxAction
{
    public static function getRulesets(): string|array
    {
        return UpdateFormBuilderFieldResponseRuleset::class;
    }

    public static function models(): array
    {
        return [FormBuilderFieldResponse::class];
    }

    public function performAction(): FormBuilderFieldResponse
    {
        $formBuilderFieldResponse = resolve_static(FormBuilderFieldResponse::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $formBuilderFieldResponse->fill($this->data);
        $formBuilderFieldResponse->save();

        return $formBuilderFieldResponse->refresh();
    }
}
