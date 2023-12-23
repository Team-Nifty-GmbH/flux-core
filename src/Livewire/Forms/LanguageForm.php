<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Language\CreateLanguage;
use FluxErp\Actions\Language\DeleteLanguage;
use FluxErp\Actions\Language\UpdateLanguage;
use Livewire\Attributes\Locked;

class LanguageForm extends FluxForm
{
    #[Locked]
    public ?int $id = null;

    public ?string $name = null;

    public ?string $iso_name = null;

    public ?string $language_code = null;

    public ?bool $is_default = false;

    protected function getActions(): array
    {
        return [
            'create' => CreateLanguage::class,
            'update' => UpdateLanguage::class,
            'delete' => DeleteLanguage::class,
        ];
    }
}
