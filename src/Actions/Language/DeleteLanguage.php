<?php

namespace FluxErp\Actions\Language;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Language;
use FluxErp\Rulesets\Language\DeleteLanguageRuleset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DeleteLanguage extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(DeleteLanguageRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [Language::class];
    }

    public function performAction(): ?bool
    {
        $language = resolve_static(Language::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $language->language_code = $language->language_code.'___'.Hash::make(Str::uuid());
        $language->save();

        return $language->delete();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $errors = [];
        $language = resolve_static(Language::class, 'query')
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
    }
}
