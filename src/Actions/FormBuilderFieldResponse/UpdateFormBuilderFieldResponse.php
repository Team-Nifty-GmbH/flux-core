<?php

namespace FluxErp\Actions\FormBuilderFieldResponse;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\FormBuilderFieldResponse;
use FluxErp\Rulesets\FormBuilderFieldResponse\UpdateFormBuilderFieldResponseRuleset;

class UpdateFormBuilderFieldResponse extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(UpdateFormBuilderFieldResponseRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [FormBuilderFieldResponse::class];
    }

    public function performAction(): FormBuilderFieldResponse
    {
        $formBuilderFieldResponse = app(FormBuilderFieldResponse::class)->query()
            ->whereKey($this->data['id'])
            ->first();

        $formBuilderFieldResponse->fill($this->data);
        $formBuilderFieldResponse->save();

        return $formBuilderFieldResponse->refresh();
    }
}
