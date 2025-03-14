<?php

namespace FluxErp\Actions\LanguageLine;

use FluxErp\Actions\FluxAction;
use FluxErp\Rulesets\LanguageLine\DeleteLanguageLineRuleset;
use Spatie\TranslationLoader\LanguageLine;

class DeleteLanguageLine extends FluxAction
{
    public static function models(): array
    {
        return [LanguageLine::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteLanguageLineRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(LanguageLine::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
