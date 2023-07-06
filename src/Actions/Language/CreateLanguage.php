<?php

namespace FluxErp\Actions\Language;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\CreateLanguageRequest;
use FluxErp\Models\Language;
use Illuminate\Support\Facades\Validator;

class CreateLanguage implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new CreateLanguageRequest())->rules();
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public static function name(): string
    {
        return 'language.create';
    }

    public static function description(): string|null
    {
        return 'create language';
    }

    public static function models(): array
    {
        return [Language::class];
    }

    public function execute(): Language
    {
        $language = new Language($this->data);
        $language->save();

        return $language;
    }

    public function setRules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function validate(): static
    {
        $this->data = Validator::validate($this->data, $this->rules);

        return $this;
    }
}
