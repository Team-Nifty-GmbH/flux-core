<?php

namespace FluxErp\Actions\Translation;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\UpdateTranslationRequest;
use Illuminate\Database\Eloquent\Model;
use Spatie\TranslationLoader\LanguageLine;

class UpdateTranslation extends BaseAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new UpdateTranslationRequest())->rules();
    }

    public static function models(): array
    {
        return [LanguageLine::class];
    }

    public function performAction(): Model
    {
        $languageLine = LanguageLine::query()
            ->whereKey($this->data['id'])
            ->first();

        $languageLine->fill($this->data);
        $languageLine->save();

        return $languageLine;
    }
}
