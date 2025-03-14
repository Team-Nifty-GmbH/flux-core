<?php

namespace FluxErp\Actions\LanguageLine;

use FluxErp\Actions\FluxAction;
use FluxErp\Rulesets\LanguageLine\CreateLanguageLineRuleset;
use Spatie\TranslationLoader\LanguageLine;

class CreateLanguageLine extends FluxAction
{
    public static function models(): array
    {
        return [LanguageLine::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateLanguageLineRuleset::class;
    }

    public function performAction(): LanguageLine
    {
        $languageLine = app(LanguageLine::class, ['attributes' => $this->data]);
        $languageLine->save();

        return $languageLine->fresh();
    }
}
