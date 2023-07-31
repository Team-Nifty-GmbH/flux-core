<?php

namespace FluxErp\Actions\Language;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\CreateLanguageRequest;
use FluxErp\Models\Language;
use Illuminate\Support\Facades\Validator;

class CreateLanguage extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new CreateLanguageRequest())->rules();
    }

    public static function models(): array
    {
        return [Language::class];
    }

    public function execute(): Language
    {
        $language = new Language($this->data);
        $language->save();

        return $language->fresh();
    }

    public function validate(): static
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new Language());

        $this->data = $validator->validate();

        return $this;
    }
}
