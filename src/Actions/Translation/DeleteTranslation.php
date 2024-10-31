<?php

namespace FluxErp\Actions\Translation;

use FluxErp\Actions\FluxAction;
use FluxErp\Rulesets\Translation\DeleteTranslationRuleset;
use Spatie\TranslationLoader\LanguageLine;

class DeleteTranslation extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return DeleteTranslationRuleset::class;
    }

    public static function models(): array
    {
        return [LanguageLine::class];
    }

    public function performAction(): ?bool
    {
        return resolve_static(LanguageLine::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
