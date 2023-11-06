<?php

namespace FluxErp\Actions\FormBuilderResponse;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\CreateFormBuilderResponseRequest;
use FluxErp\Models\FormBuilderResponse;

class CreateFormBuilderResponse extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new CreateFormBuilderResponseRequest())->rules();
    }
    public static function models(): array
    {
        return [
            FormBuilderResponse::class,
        ];
    }

    public function performAction(): FormBuilderResponse
    {
        $formBuilderResponse = new FormBuilderResponse();

        $formBuilderResponse->fill($this->data);
        $formBuilderResponse->save();

        return $formBuilderResponse->refresh();
    }

    public function validateData(): void
    {
        $validator = Validator($this->data, $this->rules);
        $validator->addModel(new FormBuilderResponse());

        $this->data = $validator->validate();
    }
}
