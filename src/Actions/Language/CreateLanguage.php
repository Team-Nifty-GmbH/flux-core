<?php

namespace FluxErp\Actions\Language;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Language;
use FluxErp\Rulesets\Language\CreateLanguageRuleset;
use Illuminate\Support\Facades\Validator;

class CreateLanguage extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(CreateLanguageRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [Language::class];
    }

    public function performAction(): Language
    {
        $this->data['is_default'] = ! Language::query()->where('is_default', true)->exists()
            ? true
            : $this->data['is_default'] ?? false;

        if ($this->data['is_default']) {
            Language::query()->update(['is_default' => false]);
        }

        $language = app(Language::class, ['attributes' => $this->data]);
        $language->save();

        return $language->fresh();
    }

    protected function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(app(Language::class));

        $this->data = $validator->validate();
    }
}
