<?php

namespace FluxErp\Actions\LanguageLine;

use FluxErp\Actions\FluxAction;
use FluxErp\Rulesets\LanguageLine\UpdateLanguageLineRuleset;
use Illuminate\Database\Eloquent\Model;
use Spatie\TranslationLoader\LanguageLine;

class UpdateLanguageLine extends FluxAction
{
    public static function models(): array
    {
        return [LanguageLine::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateLanguageLineRuleset::class;
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
