<?php

namespace FluxErp\Actions\Translation;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\CreateTranslationRequest;
use Spatie\TranslationLoader\LanguageLine;

class CreateLanguageLine extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new CreateTranslationRequest())->rules();
    }

    public static function name(): string
    {
        return 'translation.create';
    }

    public static function description(): string|null
    {
        return 'create translation';
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
