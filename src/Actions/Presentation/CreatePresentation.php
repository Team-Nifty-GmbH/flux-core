<?php

namespace FluxErp\Actions\Presentation;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\CreatePresentationRequest;
use FluxErp\Models\Presentation;
use Illuminate\Support\Facades\Validator;

class CreatePresentation extends BaseAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new CreatePresentationRequest())->rules();
    }

    public static function models(): array
    {
        return [Presentation::class];
    }

    public function performAction(): Presentation
    {
        $presentation = new Presentation($this->data);
        $presentation->save();

        return $presentation;
    }

    public function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new Presentation());

        $this->data = $validator->validate();
    }
}
