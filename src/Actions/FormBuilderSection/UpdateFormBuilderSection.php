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
        $formBuilderSection = FormBuilderSection::query()
            ->whereKey($this->data['id'])
            ->first();

        $formBuilderSection->fill($this->data);
        $formBuilderSection->save();

        return $formBuilderSection->refresh();
    }
}
