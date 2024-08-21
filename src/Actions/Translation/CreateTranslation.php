<?php

namespace FluxErp\Actions\Translation;

use FluxErp\Actions\FluxAction;
use FluxErp\Rulesets\Translation\CreateTranslationRuleset;
use Spatie\TranslationLoader\LanguageLine;

class CreateTranslation extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(CreateTranslationRuleset::class, 'getRules');
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
