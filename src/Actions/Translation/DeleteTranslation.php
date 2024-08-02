<?php

namespace FluxErp\Actions\Translation;

use FluxErp\Actions\FluxAction;
use FluxErp\Rulesets\Translation\DeleteTranslationRuleset;
use Spatie\TranslationLoader\LanguageLine;

class DeleteTranslation extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(DeleteTranslationRuleset::class, 'getRules');
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
