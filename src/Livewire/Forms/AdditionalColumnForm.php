<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\AdditionalColumn\CreateAdditionalColumn;
use FluxErp\Actions\AdditionalColumn\DeleteAdditionalColumn;
use FluxErp\Actions\AdditionalColumn\UpdateAdditionalColumn;
use Livewire\Attributes\Locked;

class AdditionalColumnForm extends FluxForm
{
    #[Locked]
    public ?int $id = null;

    public ?string $name = null;

    public ?string $model_type = null;

    public ?int $model_id = null;

    public ?string $field_type = null;

    public ?string $label = null;

    public ?array $validations = null;

    public ?array $values = null;

    public bool $is_translatable = false;

    public bool $is_customer_editable = false;

    public bool $is_frontend_visible = true;

    protected function getActions(): array
    {
        return [
            'create' => CreateAdditionalColumn::class,
            'update' => UpdateAdditionalColumn::class,
            'delete' => DeleteAdditionalColumn::class,
        ];
    }
}
