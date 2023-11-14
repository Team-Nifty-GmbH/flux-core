<?php

namespace FluxErp\Actions\FormBuilderFieldResponse;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\UpdateFormBuilderFieldResponseRequest;
use FluxErp\Models\FormBuilderFieldResponse;

class UpdateFormBuilderFieldResponse extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new UpdateFormBuilderFieldResponseRequest())->rules();
    }

    public static function models(): array
    {
        return [FormBuilderFieldResponse::class];
    }

    public function performAction(): FormBuilderFieldResponse
    {
        $formBuilderFieldResponse = FormBuilderFieldResponse::query()
            ->whereKey($this->data['id'])
            ->first();

        $formBuilderFieldResponse->fill($this->data);
        $formBuilderFieldResponse->save();

        return $formBuilderFieldResponse->refresh();
    }
}
