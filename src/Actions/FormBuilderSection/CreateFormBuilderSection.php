<?php

namespace FluxErp\Actions\FormBuilderSection;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\CreateFormBuilderSectionRequest;
use FluxErp\Models\FormBuilderSection;

class CreateFormBuilderSection extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
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
}
