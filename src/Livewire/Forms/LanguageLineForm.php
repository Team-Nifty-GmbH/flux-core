<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\LanguageLine\CreateLanguageLine;
use FluxErp\Actions\LanguageLine\DeleteLanguageLine;
use FluxErp\Actions\LanguageLine\UpdateLanguageLine;
use Livewire\Attributes\Locked;

class LanguageLineForm extends FluxForm
{
    public string $group = '*';

    #[Locked]
    public ?int $id = null;

    public ?string $key = null;

    public ?string $locale = null;

    public ?array $text = [];

    public ?string $translation = null;

    public function fill($values): void
    {
        $this->locale = data_get(array_keys(data_get($values, 'text', [])), 0);
        $this->translation = data_get($values, 'text.' . $this->locale);

        parent::fill($values);
    }

    public function toActionData(): array
    {
        $this->text = [
            $this->locale => $this->translation,
        ];

        return parent::toActionData();
    }

    protected function getActions(): array
    {
        return [
            'create' => CreateLanguageLine::class,
            'update' => UpdateLanguageLine::class,
            'delete' => DeleteLanguageLine::class,
        ];
    }
}
