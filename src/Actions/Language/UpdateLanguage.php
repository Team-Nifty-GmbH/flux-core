<?php

namespace FluxErp\Actions\Language;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\UpdateLanguageRequest;
use FluxErp\Models\Language;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class UpdateLanguage extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new UpdateLanguageRequest())->rules();

        $this->rules['language_code'] = $this->rules['language_code'] . ',' . $this->data['id'];
        $this->rules['iso_name'] = $this->rules['iso_name'] . ',' . $this->data['id'];
    }

    public static function models(): array
    {
        return [Language::class];
    }

    public function performAction(): Model
    {
        $this->data['is_default'] = ! Language::query()->where('is_default', true)->exists()
            ? true
            : $this->data['is_default'] ?? false;

        if ($this->data['is_default']) {
            Language::query()->update(['is_default' => false]);
        }

        $language = Language::query()
            ->whereKey($this->data['id'])
            ->first();

        $language->fill($this->data);
        $language->save();

        return $language->withoutRelations()->fresh();
    }

    public function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new Language());

        $this->data = $validator->validate();
    }
}
