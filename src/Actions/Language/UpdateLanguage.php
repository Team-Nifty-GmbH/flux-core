<?php

namespace FluxErp\Actions\Language;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Language;
use FluxErp\Rulesets\Language\UpdateLanguageRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class UpdateLanguage extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(UpdateLanguageRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [Language::class];
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
        $this->rules['language_code'] .= ','.($this->data['id'] ?? 0);
        $this->rules['iso_name'] .= ','.($this->data['id'] ?? 0);

        if (($this->data['is_default'] ?? false)
            && ! resolve_static(Language::class, 'query')
                ->whereKeyNot($this->data['id'] ?? 0)
                ->where('is_default', true)
                ->exists()
        ) {
            $this->rules['is_default'] .= '|accepted';
        }
    }

    protected function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(app(Language::class));

        $this->data = $validator->validate();
    }
}
