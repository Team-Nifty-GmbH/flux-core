<?php

namespace FluxErp\Actions\Language;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Language;
use FluxErp\Rulesets\Language\CreateLanguageRuleset;

class CreateLanguage extends FluxAction
{
    public static function models(): array
    {
        return [Language::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateLanguageRuleset::class;
    }

    public function performAction(): Language
    {
        $language = app(Language::class, ['attributes' => $this->data]);
        $language->save();

        return $language->fresh();
    }
}
