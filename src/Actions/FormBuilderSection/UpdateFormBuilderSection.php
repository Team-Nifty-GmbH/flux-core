<?php

namespace FluxErp\Actions\FormBuilderSection;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\FormBuilderSection;
use FluxErp\Rulesets\FormBuilderSection\UpdateFormBuilderSectionRuleset;

class UpdateFormBuilderSection extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(UpdateFormBuilderSectionRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [FormBuilderSection::class];
    }

    public function performAction(): FormBuilderSection
    {
        $formBuilderSection = resolve_static(FormBuilderSection::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $formBuilderSection->fill($this->data);
        $formBuilderSection->save();

        return $formBuilderSection->refresh();
    }
}
