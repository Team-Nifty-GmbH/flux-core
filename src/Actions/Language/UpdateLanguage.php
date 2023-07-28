<?php

namespace FluxErp\Actions\Language;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\UpdateLanguageRequest;
use FluxErp\Models\Language;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class UpdateLanguage extends BaseAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new UpdateLanguageRequest())->rules();
    }

    public static function models(): array
    {
        return [Language::class];
    }

    public function performAction(): Model
    {
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
