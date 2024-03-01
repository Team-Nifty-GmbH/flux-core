<?php

namespace FluxErp\Actions\FormBuilderSection;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\FormBuilderSection;
use FluxErp\Rulesets\FormBuilderSection\CreateFormBuilderSectionRuleset;

class CreateFormBuilderSection extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(CreateFormBuilderSectionRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [FormBuilderSection::class];
    }

    public function performAction(): FormBuilderSection
    {
        $formBuilderSection = app(FormBuilderSection::class, ['attributes' => $this->data]);
        $formBuilderSection->save();

        return $formBuilderSection->refresh();
    }
}
