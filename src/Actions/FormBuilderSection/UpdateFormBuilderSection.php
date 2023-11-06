<?php

namespace FluxErp\Actions\FormBuilderSection;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\UpdateFormBuilderSectionRequest;
use FluxErp\Models\FormBuilderSection;

class UpdateFormBuilderSection extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new UpdateFormBuilderSectionRequest())->rules();
    }

    public static function models(): array
    {
        return [FormBuilderSection::class];
    }

    public function performAction(): FormBuilderSection
    {
        $formBuilderSection = FormBuilderSection::find($this->data['id']);

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
