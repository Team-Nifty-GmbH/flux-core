<?php

namespace FluxErp\Actions\Translation;

use FluxErp\Actions\FluxAction;
use Spatie\TranslationLoader\LanguageLine;

class DeleteTranslation extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:language_lines,id',
        ];
    }

    public static function models(): array
    {
        return [LanguageLine::class];
    }

    public function performAction(): ?bool
    {
        return LanguageLine::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
