<?php

namespace FluxErp\Actions\Translation;

use FluxErp\Actions\FluxAction;
use FluxErp\Rulesets\Translation\CreateTranslationRuleset;
use Spatie\TranslationLoader\LanguageLine;

class CreateTranslation extends FluxAction
{
    public static function getRulesets(): string|array
    {
        return CreateTranslationRuleset::class;
    }

    public static function models(): array
    {
        return [LanguageLine::class];
    }

    public function performAction(): LanguageLine
    {
        $languageLine = app(LanguageLine::class, ['attributes' => $this->data]);
        $languageLine->save();

        return $languageLine->fresh();
    }
}
