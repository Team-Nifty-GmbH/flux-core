<?php

namespace FluxErp\Actions\Language;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Language;
use FluxErp\Rulesets\Language\UpdateLanguageRuleset;
use Illuminate\Database\Eloquent\Model;

class UpdateLanguage extends FluxAction
{
    public static function models(): array
    {
        return [Language::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateLanguageRuleset::class;
    }

    public function performAction(): Model
    {
        $language = resolve_static(Language::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $language->fill($this->data);
        $language->save();

        return $language->withoutRelations()->fresh();
    }

    protected function prepareForValidation(): void
    {
        $this->rules['language_code'] .= ',' . ($this->data['id'] ?? 0);
        $this->rules['iso_name'] .= ',' . ($this->data['id'] ?? 0);

        if (($this->data['is_default'] ?? false)
            && ! resolve_static(Language::class, 'query')
                ->whereKeyNot($this->data['id'] ?? 0)
                ->where('is_default', true)
                ->exists()
        ) {
            $this->rules['is_default'] .= '|accepted';
        }
    }
}
