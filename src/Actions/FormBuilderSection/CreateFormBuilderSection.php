<?php

namespace FluxErp\Actions\FormBuilderSection;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\CreateFormBuilderSectionRequest;
use FluxErp\Models\FormBuilderSection;

class CreateFormBuilderSection extends FluxAction
{
    protected function  boot(array $boot):void
    {
        parent::boot($boot);
        $this->rules = (new CreateFormBuilderSectionRequest())->rules();
    }

    public static function models(): array
    {
        return [FormBuilderSection::class];
    }

    public function performAction(): FormBuilderSection
    {
        $formBuilderSection = new FormBuilderSection();

        $formBuilderSection->fill($this->data);
        $formBuilderSection->save();
        return $formBuilderSection->refresh();
    }

    public function validateData(): void
    {
        $validator = Validator($this->data, $this->rules);
        $validator->addModel(new FormBuilderSection());

        $this->data = $validator->validate();
    }
}
