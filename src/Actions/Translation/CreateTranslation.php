<?php

namespace FluxErp\Actions\Translation;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\CreateTranslationRequest;
use Spatie\TranslationLoader\LanguageLine;

class CreateTranslation extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new CreateTranslationRequest())->rules();
    }

    public static function models(): array
    {
        return [LanguageLine::class];
    }

    public function execute(): LanguageLine
    {
        return LanguageLine::create($this->data);
    }
}
