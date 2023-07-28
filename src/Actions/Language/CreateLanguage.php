<?php

namespace FluxErp\Actions\Language;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\CreateLanguageRequest;
use FluxErp\Models\Language;
use Illuminate\Support\Facades\Validator;

class CreateLanguage extends BaseAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new CreateLanguageRequest())->rules();
    }

    public static function models(): array
    {
        return [Language::class];
    }

    public function performAction(): Language
    {
        $language = new Language($this->data);
        $language->save();

        return $language;
    }

    public function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new Language());

        $this->data = $validator->validate();
    }
}
