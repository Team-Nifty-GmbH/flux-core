<?php

namespace FluxErp\Actions\Translation;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\UpdateTranslationRequest;
use Illuminate\Database\Eloquent\Model;
use Spatie\TranslationLoader\LanguageLine;

class UpdateLanguageLine extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new UpdateTranslationRequest())->rules();
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
}
