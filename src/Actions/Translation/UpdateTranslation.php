<?php

namespace FluxErp\Actions\Translation;

use FluxErp\Actions\FluxAction;
use FluxErp\Rulesets\Translation\UpdateTranslationRuleset;
use Illuminate\Database\Eloquent\Model;
use Spatie\TranslationLoader\LanguageLine;

class UpdateTranslation extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return UpdateTranslationRuleset::class;
    }

    public static function models(): array
    {
        return [LanguageLine::class];
    }

    public function performAction(): Model
    {
        $languageLine = resolve_static(LanguageLine::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $languageLine->fill($this->data);
        $languageLine->save();

        return $languageLine;
    }
}
