<?php

namespace FluxErp\Actions\Translation;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\CreateTranslationRequest;
use Spatie\TranslationLoader\LanguageLine;

class CreateTranslation extends BaseAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new CreateTranslationRequest())->rules();
    }

    public static function models(): array
    {
        return [LanguageLine::class];
    }

    public function performAction(): LanguageLine
    {
        return LanguageLine::create($this->data);
    }
}
