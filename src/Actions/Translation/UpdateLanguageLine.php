<?php

namespace FluxErp\Actions\Translation;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\UpdateTranslationRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Spatie\TranslationLoader\LanguageLine;

class UpdateLanguageLine implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new UpdateTranslationRequest())->rules();
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public static function name(): string
    {
        return 'translation.update';
    }

    public static function description(): string|null
    {
        return 'update translation';
    }

    public static function models(): array
    {
        return [LanguageLine::class];
    }

    public function execute(): Model
    {
        $languageLine = LanguageLine::query()
            ->whereKey($this->data['id'])
            ->first();

        $languageLine->fill($this->data);
        $languageLine->save();

        return $languageLine;
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
