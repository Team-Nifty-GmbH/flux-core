<?php

namespace FluxErp\Actions\FormBuilderSection;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\FormBuilderSection;
use FluxErp\Rulesets\FormBuilderSection\CreateFormBuilderSectionRuleset;

class CreateFormBuilderSection extends FluxAction
{
    public static function models(): array
    {
        return [FormBuilderSection::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateFormBuilderSectionRuleset::class;
    }

    public function performAction(): FormBuilderSection
    {
        $formBuilderSection = app(FormBuilderSection::class, ['attributes' => $this->data]);
        $formBuilderSection->save();

        return $formBuilderSection->refresh();
    }
}
