<?php

namespace FluxErp\Actions\Translation;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\CreateTranslationRequest;
use Illuminate\Support\Facades\Validator;
use Spatie\TranslationLoader\LanguageLine;

class CreateLanguageLine implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new CreateTranslationRequest())->rules();
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'translation.create';
    }

    public static function description(): string|null
    {
        return 'create translation';
    }

    public static function models(): array
    {
        return [LanguageLine::class];
    }

    public function execute(): LanguageLine
    {
        return LanguageLine::create($this->data);
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
