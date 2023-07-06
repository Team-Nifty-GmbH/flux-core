<?php

namespace FluxErp\Actions\Translation;

use FluxErp\Contracts\ActionInterface;
use Illuminate\Support\Facades\Validator;
use Spatie\TranslationLoader\LanguageLine;

class DeleteLanguageLine implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = [
            'id' => 'required|integer|exists:language_lines,id',
        ];
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'translation.delete';
    }

    public static function description(): string|null
    {
        return 'delete translation';
    }

    public static function models(): array
    {
        return [LanguageLine::class];
    }

    public function execute()
    {
        return LanguageLine::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
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
