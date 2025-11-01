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
    public static function models(): array
    {
        return [Language::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteLanguageRuleset::class;
    }

    public function performAction(): ?bool
    {
        $language = resolve_static(Language::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $language->language_code = $language->language_code . '___' . Hash::make(Str::uuid());
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
                'address' => ['Language referenced by an address'],
            ];
        }

        if ($language->users()->exists()) {
            $errors += [
                'user' => ['Language referenced by a user'],
            ];
        }

        if ($errors) {
            throw ValidationException::withMessages($errors)
                ->errorBag('deleteLanguage')
                ->status(423);
        }
    }
}
