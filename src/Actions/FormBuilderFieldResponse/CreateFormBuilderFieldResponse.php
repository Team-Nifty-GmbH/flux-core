<?php

namespace FluxErp\Actions\FormBuilderFieldResponse;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\CreateFormBuilderFieldResponseRequest;
use FluxErp\Models\FormBuilderFieldResponse;

class CreateFormBuilderFieldResponse extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new CreateFormBuilderFieldResponseRequest())->rules();
    }

    public static function models(): array
    {
        return [FormBuilderFieldResponse::class];
    }

    public function performAction(): FormBuilderFieldResponse
    {
        $formBuilderFieldResponse = new FormBuilderFieldResponse();

        $formBuilderFieldResponse->fill($this->data);
        $formBuilderFieldResponse->save();

        return $formBuilderFieldResponse->refresh();
    }
}
