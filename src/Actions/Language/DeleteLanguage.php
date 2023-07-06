<?php

namespace FluxErp\Actions\Language;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Models\Language;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DeleteLanguage implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = [
            'id' => 'required|integer|exists:languages,id,deleted_at,NULL',
        ];
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public static function name(): string
    {
        return 'language.delete';
    }

    public static function description(): string|null
    {
        return 'delete language';
    }

    public static function models(): array
    {
        return [Language::class];
    }

    public function execute(): bool|null
    {
        $language = Language::query()
            ->whereKey($this->data['id'])
            ->first();

        $language->language_code = $language->language_code . '___' . Hash::make(Str::uuid());
        $language->save();

        return $language->delete();
    }

    public function setRules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function validate(): static
    {
        $this->data = Validator::validate($this->data, $this->rules);

        $errors = [];
        $language = Language::query()
            ->whereKey($this->data['id'])
            ->first();

        // Don't delete if in use.
        if ($language->addresses()->exists()) {
            $errors += [
                'address' => [__('Language referenced by an address')],
            ];
        }

        if ($language->users()->exists()) {
            $errors += [
                'user' => [__('Language referenced by a user')],
            ];
        }

        if ($errors) {
            throw ValidationException::withMessages($errors)->errorBag('deleteLanguage');
        }

        return $this;
    }
}
