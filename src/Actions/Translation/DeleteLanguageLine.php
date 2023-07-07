<?php

namespace FluxErp\Actions\Translation;

use FluxErp\Actions\BaseAction;
use Spatie\TranslationLoader\LanguageLine;

class DeleteLanguageLine extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = [
            'id' => 'required|integer|exists:language_lines,id',
        ];
    }

    public static function name(): string
    {
        return 'translation.delete';
    }

    public static function description(): string|null
    {
        return 'delete translation';
    }

    public static function models(): array
    {
        return [LanguageLine::class];
    }

    public function execute(): bool|null
    {
        return LanguageLine::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
